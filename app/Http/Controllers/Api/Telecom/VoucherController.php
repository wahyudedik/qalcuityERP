<?php

namespace App\Http\Controllers\Api\Telecom;

use App\Models\Customer;
use App\Models\InternetPackage;
use App\Models\VoucherCode;
use App\Services\Telecom\VoucherGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherController extends TelecomApiController
{
    protected VoucherGenerationService $voucherService;

    public function __construct()
    {
        $this->voucherService = new VoucherGenerationService();
    }

    /**
     * Generate voucher codes.
     * 
     * POST /api/telecom/vouchers/generate
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'package_id' => 'required|exists:internet_packages,id',
                'quantity' => 'required|integer|min:1|max:1000',
                'code_length' => 'nullable|integer|min:6|max:16',
                'code_pattern' => 'nullable|string|in:numeric,alphabetic,alphanumeric',
                'validity_hours' => 'nullable|integer|min:1',
                'max_usage' => 'nullable|integer|min:1',
                'sale_price' => 'nullable|numeric|min:0',
                'batch_number' => 'nullable|string|max:255',
                'generated_by' => 'nullable|integer|exists:users,id',
            ]);

            $package = InternetPackage::where('id', $validated['package_id'])
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail();

            $options = [
                'code_length' => $validated['code_length'] ?? 8,
                'code_pattern' => $validated['code_pattern'] ?? 'alphanumeric',
                'validity_hours' => $validated['validity_hours'] ?? 24,
                'max_usage' => $validated['max_usage'] ?? 1,
                'sale_price' => $validated['sale_price'] ?? null,
                'batch_number' => $validated['batch_number'] ?? null,
                'generated_by' => $validated['generated_by'] ?? auth()->id(),
                'valid_from' => now(),
                'valid_until' => now()->addHours($validated['validity_hours'] ?? 24),
            ];

            if ($validated['quantity'] == 1) {
                $vouchers = [$this->voucherService->generateSingle($package, $options)];
            } else {
                $vouchers = $this->voucherService->generateBatch($package, $validated['quantity'], $options);
            }

            $this->logApiRequest($request, 'POST /api/telecom/vouchers/generate', [
                'package_id' => $package->id,
                'quantity' => count($vouchers)
            ]);

            return $this->success([
                'vouchers' => collect($vouchers)->map(function ($voucher) {
                    return [
                        'code' => $voucher->code,
                        'package_name' => $voucher->package->name,
                        'valid_until' => $voucher->valid_until,
                        'max_usage' => $voucher->max_usage,
                        'sale_price' => $voucher->sale_price,
                    ];
                }),
                'total_generated' => count($vouchers),
                'batch_number' => $vouchers[0]->batch_number,
            ], 'Vouchers generated successfully', 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error("Failed to generate vouchers", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error('Failed to generate vouchers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Redeem/use a voucher code.
     * 
     * POST /api/telecom/vouchers/redeem
     */
    public function redeem(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'customer_id' => 'nullable|exists:customers,id',
                'username' => 'nullable|string',
            ]);

            $customer = null;
            if ($validated['customer_id']) {
                $customer = Customer::where('id', $validated['customer_id'])
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->first();

                if (!$customer) {
                    return $this->error('Customer not found or unauthorized', 404);
                }
            }

            $result = $this->voucherService->redeemVoucher(
                $validated['code'],
                $customer,
                $validated['username'] ?? null
            );

            if (!$result['success']) {
                return $this->error($result['error'], 400);
            }

            $this->logApiRequest($request, 'POST /api/telecom/vouchers/redeem', [
                'code' => $validated['code']
            ]);

            return $this->success([
                'voucher' => $result['voucher'],
                'package' => $result['package'],
                'message' => $result['message'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error("Failed to redeem voucher", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error('Failed to redeem voucher: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get voucher statistics.
     * 
     * GET /api/telecom/vouchers/stats
     */
    public function stats(Request $request)
    {
        try {
            $batchNumber = $request->get('batch_number');
            $stats = $this->voucherService->getVoucherStats(auth()->user()->tenant_id, $batchNumber);

            $this->logApiRequest($request, 'GET /api/telecom/vouchers/stats');

            return $this->success($stats);

        } catch (\Exception $e) {
            Log::error("Failed to get voucher stats", [
                'error' => $e->getMessage()
            ]);
            return $this->error('Failed to get voucher stats: ' . $e->getMessage(), 500);
        }
    }
}
