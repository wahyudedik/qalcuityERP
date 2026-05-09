<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCertificate;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    /**
     * POST /products/{product}/certificates
     *
     * Issue a new certificate for the given product.
     * Requirements: 2.1, 2.4, 2.8
     */
    public function issue(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $expiresAt = $request->input('expires_at')
            ? Carbon::parse($request->input('expires_at'))
            : null;

        $certificate = $this->certificateService->issue(
            $product,
            $this->authenticatedUser(),
            $expiresAt
        );

        return response()->json([
            'success' => true,
            'message' => 'Sertifikat berhasil diterbitkan.',
            'certificate' => $certificate,
        ], 201);
    }

    /**
     * GET /products/{product}/certificates
     *
     * List all certificates (history) for the given product.
     * Requirements: 4.1, 4.3
     */
    public function index(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $certificates = $product->certificates()
            ->with(['issuer', 'revoker'])
            ->latest('issued_at')
            ->get();

        return response()->json([
            'success' => true,
            'certificates' => $certificates,
        ]);
    }

    /**
     * DELETE /certificates/{certificate}/revoke
     *
     * Revoke an active certificate.
     * Requirements: 4.1
     */
    public function revoke(Request $request, ProductCertificate $certificate)
    {
        abort_unless($certificate->product->tenant_id === $this->tenantId(), 403);

        $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $this->certificateService->revoke(
                $certificate,
                $this->authenticatedUser(),
                $request->input('reason')
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sertifikat berhasil dicabut.',
        ]);
    }

    /**
     * GET /certificates/{certificate}/pdf
     *
     * Download the certificate as a PDF.
     * Requirements: 2.7
     */
    public function pdf(Request $request, ProductCertificate $certificate)
    {
        abort_unless($certificate->product->tenant_id === $this->tenantId(), 403);

        $pdf = $this->certificateService->generatePdf($certificate);

        $filename = 'certificate-'.$certificate->certificate_number.'.pdf';

        return $pdf->download($filename);
    }
}
