<?php

namespace App\Http\Controllers;

use App\Models\ZeroInputLog;
use App\Services\ZeroInputService;
use Illuminate\Http\Request;

class ZeroInputController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function __construct(protected ZeroInputService $service) {}

    public function index()
    {
        $logs = ZeroInputLog::where('tenant_id', $this->tid())
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
                'success'        => $log->status === 'mapped',
                'log_id'         => $log->id,
                'status'         => $log->status,
                'mapped_module'  => $log->mapped_module,
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
            'text'    => 'required|string|max:2000',
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
                'success'        => $log->status === 'mapped',
                'log_id'         => $log->id,
                'status'         => $log->status,
                'mapped_module'  => $log->mapped_module,
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
     */
    public function confirm(Request $request, ZeroInputLog $zeroInputLog)
    {
        abort_if($zeroInputLog->tenant_id !== $this->tid(), 403);

        // Merge data yang diedit user
        if ($request->has('extracted_data')) {
            $zeroInputLog->update(['extracted_data' => $request->extracted_data]);
        }

        $result = $this->service->createRecord($zeroInputLog);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message'] ?? ($result['success'] ? 'Record berhasil dibuat.' : 'Gagal membuat record.')
        );
    }
}
