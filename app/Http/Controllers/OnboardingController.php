<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Services\ERP\OnboardingTools;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    public function show()
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        // Sudah onboarding atau bukan admin → skip
        if (!$tenant || $tenant->onboarding_completed || !$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.wizard', compact('tenant'));
    }

    public function complete(Request $request)
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        abort_if(!$tenant || !$user->isAdmin(), 403);

        $data = $request->validate([
            'business_name'       => 'required|string|max:255',
            'business_type'       => 'nullable|string|max:50',
            'business_description'=> 'nullable|string|max:500',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:500',
            'warehouse_name'      => 'required|string|max:255',
            'products'            => 'nullable|array|max:10',
            'products.*.name'     => 'required_with:products|string|max:255',
            'products.*.price'    => 'nullable|numeric|min:0',
            'products.*.unit'     => 'nullable|string|max:20',
            'expense_categories'  => 'nullable|string',
        ]);

        // Update tenant info
        $tenant->update([
            'name'                 => $data['business_name'],
            'business_type'        => $data['business_type'] ?? $tenant->business_type,
            'business_description' => $data['business_description'] ?? null,
            'phone'                => $data['phone'] ?? null,
            'address'              => $data['address'] ?? null,
            'onboarding_completed' => true,
        ]);

        // Buat gudang utama
        $warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data['warehouse_name']],
            [
                'code'      => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['warehouse_name']), 0, 4)) . '-01',
                'is_active' => true,
            ]
        );

        // Buat produk awal
        foreach ($data['products'] ?? [] as $item) {
            $name = trim($item['name'] ?? '');
            if (!$name) continue;

            $product = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                [
                    'sku'        => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6)) . '-' . rand(100, 999),
                    'price_sell' => $item['price'] ?? 0,
                    'price_buy'  => 0,
                    'unit'       => $item['unit'] ?? 'pcs',
                    'stock_min'  => 5,
                    'is_active'  => true,
                ]
            );

            ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0]
            );
        }

        // Buat kategori pengeluaran
        $categories = array_filter(array_map('trim', explode(',', $data['expense_categories'] ?? '')));
        if (empty($categories)) {
            $categories = ['Bahan Baku', 'Operasional', 'Gaji Karyawan'];
        }
        foreach ($categories as $catName) {
            if (!$catName) continue;
            ExpenseCategory::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $catName],
                [
                    'code'      => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catName), 0, 5)) . '-' . rand(10, 99),
                    'type'      => 'expense',
                    'is_active' => true,
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Setup awal selesai! Selamat datang di Qalcuity ERP.');
    }

    public function skip()
    {
        $tenant = auth()->user()->tenant;
        if ($tenant) {
            $tenant->update(['onboarding_completed' => true]);
        }
        return redirect()->route('dashboard');
    }

    /**
     * AI-powered onboarding chat endpoint.
     * Hanya menggunakan OnboardingTools (bukan semua tools ERP).
     */
    public function aiChat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
        ]);

        $user   = auth()->user();
        $tenant = $user->tenant;

        abort_if(!$tenant || !$user->isAdmin(), 403);

        $history = $request->input('history', []);
        $message = $request->input('message');

        // Build tool declarations dari OnboardingTools saja
        $toolDeclarations = OnboardingTools::definitions();

        // System prompt khusus onboarding — singkat dan fokus
        $systemPrompt = <<<PROMPT
Kamu adalah asisten setup bisnis Qalcuity ERP yang ramah dan efisien.
Tugasmu: bantu user baru setup bisnis mereka dalam beberapa langkah singkat.

Nama bisnis: {$tenant->name}
Jenis bisnis: {$tenant->business_type}

ALUR SETUP:
1. Sapa user, tanyakan konfirmasi jenis bisnis mereka
2. Tawarkan untuk apply template industri yang sesuai (gunakan apply_industry_template)
3. Tanyakan apakah ada produk/layanan spesifik yang ingin ditambahkan (gunakan setup_business)
4. Setelah setup selesai, tampilkan ringkasan dan ucapkan selamat

ATURAN:
- Gunakan Bahasa Indonesia yang ramah dan singkat
- Jangan tanya terlalu banyak pertanyaan sekaligus — satu pertanyaan per giliran
- Setelah apply_industry_template atau setup_business berhasil, sertakan teks: [SETUP_COMPLETE]
- Jika user sudah puas atau minta selesai, sertakan [SETUP_COMPLETE] di respons
- Jangan jelaskan teknis, fokus pada bisnis user
PROMPT;

        try {
            $gemini = new GeminiService();

            // Inject system prompt khusus onboarding
            $gemini->withTenantContext($systemPrompt);

            $response = $gemini->chatWithTools(
                message: $message,
                history: $history,
                toolDeclarations: $toolDeclarations,
            );

            $functionCalls = $response['function_calls'] ?? [];
            $setupComplete = false;

            if (empty($functionCalls)) {
                $text = $response['text'] ?: 'Maaf, coba ulangi pertanyaan Anda.';
                $setupComplete = str_contains($text, '[SETUP_COMPLETE]');
                $text = str_replace('[SETUP_COMPLETE]', '', $text);

                if ($setupComplete) {
                    $tenant->update(['onboarding_completed' => true]);
                }

                return response()->json([
                    'message'        => trim($text),
                    'setup_complete' => $setupComplete,
                ]);
            }

            // Eksekusi tool calls
            $tools = new OnboardingTools($tenant->id, $user->id);
            $functionResults = [];

            foreach ($functionCalls as $call) {
                $toolName = $call['name'];
                $args     = $call['args'];

                $result = match ($toolName) {
                    'setup_business'          => $tools->setupBusiness($args),
                    'apply_industry_template' => $tools->applyIndustryTemplate($args),
                    'get_industry_shortcuts'  => $tools->getIndustryShortcuts($args),
                    default                   => ['status' => 'error', 'message' => "Tool {$toolName} tidak dikenal."],
                };

                $result['_args'] = $args;
                $functionResults[] = ['name' => $toolName, 'data' => $result];

                // Jika setup_business atau apply_industry_template berhasil → tandai complete
                if (in_array($toolName, ['setup_business', 'apply_industry_template'])
                    && ($result['status'] ?? '') === 'success') {
                    $setupComplete = true;
                }
            }

            // Kirim hasil tool kembali ke Gemini untuk dirangkai
            $finalResponse = $gemini->sendFunctionResults(
                originalMessage: $message,
                history: $history,
                toolDeclarations: $toolDeclarations,
                functionResults: $functionResults,
            );

            $finalText = $finalResponse['text'] ?? '';

            // Cek [SETUP_COMPLETE] di respons final
            if (str_contains($finalText, '[SETUP_COMPLETE]')) {
                $setupComplete = true;
                $finalText = str_replace('[SETUP_COMPLETE]', '', $finalText);
            }

            if ($setupComplete) {
                $tenant->update(['onboarding_completed' => true]);
            }

            return response()->json([
                'message'        => trim($finalText) ?: 'Setup berhasil!',
                'setup_complete' => $setupComplete,
            ]);

        } catch (\Throwable $e) {
            Log::error('OnboardingController aiChat error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan. Silakan coba lagi atau gunakan setup manual.',
                'error'   => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        }
    }
}
