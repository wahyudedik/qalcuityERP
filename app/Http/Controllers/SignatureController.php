<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DigitalSignature;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function pad(string $modelType, int $modelId)
    {
        $modelClass = 'App\\Models\\'.ucfirst($modelType);
        abort_if(! class_exists($modelClass), 404);

        $model = $modelClass::findOrFail($modelId);
        abort_if(isset($model->tenant_id) && $model->tenant_id !== auth()->user()->tenant_id, 403);

        $existing = DigitalSignature::where('model_type', $modelClass)
            ->where('model_id', $modelId)
            ->with('user')
            ->get();

        return view('signature.pad', compact('model', 'modelType', 'modelId', 'existing'));
    }

    public function sign(Request $request, string $modelType, int $modelId)
    {
        $request->validate(['signature_data' => 'required|string']);

        $modelClass = 'App\\Models\\'.ucfirst($modelType);
        abort_if(! class_exists($modelClass), 404);

        $model = $modelClass::findOrFail($modelId);
        abort_if(isset($model->tenant_id) && $model->tenant_id !== auth()->user()->tenant_id, 403);

        DigitalSignature::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'model_type' => $modelClass,
            'model_id' => $modelId,
            'signature_data' => $request->signature_data,
            'ip_address' => $request->ip(),
            'signed_at' => now(),
        ]);

        ActivityLog::record('document_signed', "Dokumen {$modelType} #{$modelId} ditandatangani");

        return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil ditandatangani.']);
    }
}
