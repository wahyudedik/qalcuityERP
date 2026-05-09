<?php

namespace App\Http\Controllers;

use App\Models\ZeroInputLog;
use App\Services\ZeroInputService;
use Illuminate\Http\Request;

class ZeroInputController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function __construct(protected ZeroInputService $service) {}

    public function index()
    {
        $logs = ZeroInputLog::with('user')
            ->where('tenant_id', $this->tid())
            ->latest()
            ->paginate(20);

        return view('zero-input.index', compact('logs'));
    }

    /**
     * Upload foto nota untuk diproses OCR.
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $log = $this->service->processPhoto(
            $this->tid(),
            auth()->id(),
            $request->file('photo')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $log->status === 'mapped',
                'log_id' => $log->id,
                'status' => $log->status,
                'mapped_module' => $log->mapped_module,
                'extracted_data' => $log->extracted_data,
            ]);
        }

        return redirect()->route('zero-input.show', $log)
            ->with($log->status === 'mapped' ? 'success' : 'error',
                $log->status === 'mapped' ? 'Data berhasil diekstrak.' : 'Gagal memproses foto.');
    }

    /**
     * Proses teks (voice transcript / WhatsApp).
     */
    public function processText(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:2000',
            'channel' => 'nullable|in:voice,whatsapp,manual',
        ]);

        $log = $this->service->processText(
            $this->tid(),
            auth()->id(),
            $request->text,
            $request->channel ?? 'manual'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $log->status === 'mapped',
                'log_id' => $log->id,
                'status' => $log->status,
                'mapped_module' => $log->mapped_module,
                'extracted_data' => $log->extracted_data,
            ]);
        }

        return redirect()->route('zero-input.show', $log);
    }

    public function show(ZeroInputLog $zeroInputLog)
    {
        abort_if($zeroInputLog->tenant_id !== $this->tid(), 403);

        return view('zero-input.show', ['log' => $zeroInputLog]);
    }

    /**
     * Konfirmasi dan buat record ERP dari data yang diekstrak.
     * Tracks user corrections for feedback loop.
     */
    public function confirm(Request $request, ZeroInputLog $zeroInputLog)
    {
        abort_if($zeroInputLog->tenant_id !== $this->tid(), 403);

        // Compare original extracted_data with user-submitted data to detect corrections
        $originalData = $zeroInputLog->extracted_data ?? [];
        $userData = $request->input('extracted_data', $originalData);
        $wasCorrected = $this->detectCorrections($originalData, $userData);

        // Save user corrections separately (preserve original AI output for training)
        $zeroInputLog->update([
            'user_corrected_data' => $wasCorrected ? $userData : null,
            'was_corrected' => $wasCorrected,
            'feedback' => $wasCorrected ? 'corrected' : 'accurate',
            'extracted_data' => $userData, // use corrected data for record creation
        ]);

        $result = $this->service->createRecord($zeroInputLog);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message'] ?? ($result['success'] ? 'Record berhasil dibuat.' : 'Gagal membuat record.')
        );
    }

    /**
     * Reject OCR result — mark as inaccurate without creating record.
     */
    public function reject(Request $request, ZeroInputLog $zeroInputLog)
    {
        abort_if($zeroInputLog->tenant_id !== $this->tid(), 403);

        $zeroInputLog->update([
            'feedback' => 'rejected',
            'was_corrected' => true,
            'status' => 'rejected',
            'error_message' => $request->input('reason', 'Ditolak oleh user'),
        ]);

        return back()->with('info', 'Hasil OCR ditolak. Feedback disimpan untuk meningkatkan akurasi.');
    }

    /**
     * Compare original AI data with user-submitted data to detect corrections.
     */
    private function detectCorrections(array $original, array $corrected): bool
    {
        // Remove non-comparable keys
        $skip = ['module', 'confidence', 'raw'];

        foreach ($corrected as $key => $value) {
            if (in_array($key, $skip)) {
                continue;
            }
            $origVal = $original[$key] ?? null;

            // Normalize for comparison
            if (is_numeric($value) && is_numeric($origVal)) {
                if (abs((float) $value - (float) $origVal) > 0.01) {
                    return true;
                }
            } elseif (is_array($value) || is_array($origVal)) {
                if (json_encode($value) !== json_encode($origVal)) {
                    return true;
                }
            } elseif ((string) $value !== (string) $origVal) {
                return true;
            }
        }

        return false;
    }
}
