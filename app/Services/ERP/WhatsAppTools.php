<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'send_whatsapp',
                'description' => 'Kirim pesan WhatsApp ke customer atau nomor tertentu via Fonnte. Gunakan untuk: '
                    . '"kirim invoice ke customer Budi via WA", '
                    . '"kirim tagihan ke nomor 08123456789", '
                    . '"reminder pembayaran ke customer X via WhatsApp", '
                    . '"kirim pesan WA ke Budi: pesanan sudah siap".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'to'            => ['type' => 'string', 'description' => 'Nomor WA tujuan (08xx atau 62xx) ATAU nama customer'],
                        'message'       => ['type' => 'string', 'description' => 'Isi pesan yang akan dikirim'],
                        'invoice_number'=> ['type' => 'string', 'description' => 'Nomor invoice untuk dikirim (opsional, akan generate pesan otomatis)'],
                    ],
                    'required' => ['to'],
                ],
            ],
        ];
    }

    public function sendWhatsapp(array $args): array
    {
        $token = config('services.fonnte.token');
        if (!$token) {
            return [
                'status'  => 'error',
                'message' => 'WhatsApp belum dikonfigurasi. Tambahkan FONNTE_TOKEN di pengaturan sistem.',
            ];
        }

        // Resolve nomor dari nama customer
        $to = $args['to'] ?? '';
        if (!preg_match('/^[0-9+]/', $to)) {
            $customer = Customer::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$to}%")
                ->first();
            if (!$customer || !$customer->phone) {
                return ['status' => 'error', 'message' => "Customer '{$to}' tidak ditemukan atau tidak memiliki nomor WA."];
            }
            $to = $customer->phone;
        }

        // Normalize nomor
        $to = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($to, '0')) $to = '62' . substr($to, 1);

        // Build message
        $message = $args['message'] ?? '';

        // Auto-generate pesan invoice jika ada invoice_number
        if (!empty($args['invoice_number'])) {
            $invoice = Invoice::where('tenant_id', $this->tenantId)
                ->where('invoice_number', $args['invoice_number'])
                ->with('customer')
                ->first();

            if ($invoice) {
                $tenant = \App\Models\Tenant::find($this->tenantId);
                $message = $this->buildInvoiceMessage($invoice, $tenant);
            }
        }

        if (!$message) {
            return ['status' => 'error', 'message' => 'Isi pesan tidak boleh kosong.'];
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->post('https://api.fonnte.com/send', [
                    'target'  => $to,
                    'message' => $message,
                ]);

            $result = $response->json();

            if ($response->successful() && ($result['status'] ?? false)) {
                return [
                    'status'  => 'success',
                    'message' => "Pesan WhatsApp berhasil dikirim ke {$to}.",
                    'to'      => $to,
                    'preview' => mb_substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''),
                ];
            }

            Log::warning('Fonnte WA failed', ['response' => $result]);
            return [
                'status'  => 'error',
                'message' => 'Gagal mengirim WA: ' . ($result['reason'] ?? 'Unknown error'),
            ];

        } catch (\Throwable $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Gagal terhubung ke layanan WhatsApp.'];
        }
    }

    private function buildInvoiceMessage(Invoice $invoice, $tenant): string
    {
        $tenantName = $tenant?->name ?? 'Kami';
        $total      = 'Rp ' . number_format($invoice->total_amount, 0, ',', '.');
        $due        = $invoice->due_date?->format('d M Y') ?? '-';

        return "Halo {$invoice->customer?->name},\n\n"
            . "Berikut tagihan dari *{$tenantName}*:\n\n"
            . "📄 No. Invoice: *{$invoice->invoice_number}*\n"
            . "💰 Total: *{$total}*\n"
            . "📅 Jatuh Tempo: *{$due}*\n\n"
            . "Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo.\n\n"
            . "Terima kasih 🙏";
    }
}
