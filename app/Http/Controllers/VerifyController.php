<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function show(Request $request, string $certificateNumber)
    {
        $ipAddress = $request->ip();
        $result = $this->certificateService->verify($certificateNumber, $ipAddress);

        return view('verify.show', $result);
    }
}
