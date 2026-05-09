<?php

namespace App\Services\ERP;

use App\Models\Attendance;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'send_email_summary',
                'description' => 'Kirim ringkasan bisnis ke email pengguna. Gunakan untuk: '
                    .'"kirim ringkasan ke email saya", "email laporan hari ini", '
                    .'"kirim rekap bisnis ke email", "email summary penjualan", '
                    .'"kirim laporan mingguan ke email", "email kondisi bisnis".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => [
                            'type' => 'string',
                            'description' => 'Periode ringkasan: today, this_week, this_month (default: today)',
                        ],
                        'include' => [
                            'type' => 'string',
                            'description' => 'Bagian yang disertakan: all, sales, finance, inventory, hrm (default: all)',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function sendEmailSummary(array $args): array
    {
        $period = $args['period'] ?? 'today';
        $include = $args['include'] ?? 'all';

        $user = User::find($this->userId);
        if (! $user || ! $user->email) {
            return ['status' => 'error', 'message' => 'Email pengguna tidak ditemukan.'];
        }

        $data = $this->buildSummaryData($period, $include);
        $periodLabel = match ($period) {
            'today' => 'Hari Ini ('.now()->format('d M Y').')',
            'this_week' => 'Minggu Ini ('.now()->startOfWeek()->format('d M').' — '.now()->endOfWeek()->format('d M Y').')',
            'this_month' => 'Bulan Ini ('.now()->format('M Y').')',
            default => now()->format('d M Y'),
        };

        try {
            Mail::send([], [], function ($message) use ($user, $data, $periodLabel) {
                $message->to($user->email, $user->name)
                    ->subject("📊 Ringkasan Bisnis — {$periodLabel}")
                    ->html($this->buildEmailHtml($data, $periodLabel, $user->name));
            });

            return [
                'status' => 'success',
                'message' => "Ringkasan bisnis periode {$periodLabel} berhasil dikirim ke {$user->email}.",
                'email' => $user->email,
                'period_label' => $periodLabel,
                'data' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal mengirim email: '.$e->getMessage(),
            ];
        }
    }

    // ─── Data builder ─────────────────────────────────────────────

    private function buildSummaryData(string $period, string $include): array
    {
        [$start, $end] = $this->resolveDateRange($period);
        $data = ['periode' => $period, 'start' => $start, 'end' => $end];

        if (in_array($include, ['all', 'sales'])) {
            $orders = SalesOrder::where('tenant_id', $this->tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween('date', [$start, $end])
                ->get();
            $data['sales'] = [
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total'),
                'pending' => SalesOrder::where('tenant_id', $this->tenantId)
                    ->whereIn('status', ['pending', 'confirmed'])->count(),
            ];
        }

        if (in_array($include, ['all', 'finance'])) {
            $income = Transaction::where('tenant_id', $this->tenantId)->where('type', 'income')
                ->whereBetween('date', [$start, $end])->sum('amount');
            $expense = Transaction::where('tenant_id', $this->tenantId)->where('type', 'expense')
                ->whereBetween('date', [$start, $end])->sum('amount');
            $data['finance'] = [
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
            ];
        }

        if (in_array($include, ['all', 'inventory'])) {
            $lowStock = ProductStock::whereHas('product', fn ($q) => $q->where('tenant_id', $this->tenantId))
                ->whereColumn('quantity', '<=', 'products.price_buy') // fallback
                ->join('products', 'product_stocks.product_id', '=', 'products.id')
                ->whereColumn('product_stocks.quantity', '<=', 'products.stock_min')
                ->count();
            $data['inventory'] = [
                'total_products' => Product::where('tenant_id', $this->tenantId)->where('is_active', true)->count(),
                'low_stock' => $lowStock,
            ];
        }

        if (in_array($include, ['all', 'hrm'])) {
            $att = Attendance::where('tenant_id', $this->tenantId)
                ->whereBetween('date', [$start, $end])
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $data['hrm'] = [
                'present' => $att->get('present', 0),
                'absent' => $att->get('absent', 0),
                'late' => $att->get('late', 0),
            ];
        }

        return $data;
    }

    // ─── Email HTML builder ───────────────────────────────────────

    private function buildEmailHtml(array $data, string $periodLabel, string $userName): string
    {
        $fmt = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

        $salesHtml = '';
        if (isset($data['sales'])) {
            $s = $data['sales'];
            $salesHtml = "
            <tr><td colspan='2' style='padding:12px 0 6px;font-weight:600;color:#1e40af;font-size:14px;border-top:1px solid #e2e8f0'>🛒 Penjualan</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Total Order</td><td style='text-align:right;font-weight:600;font-size:13px'>{$s['total_orders']} order</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Total Omzet</td><td style='text-align:right;font-weight:600;font-size:13px;color:#16a34a'>{$fmt($s['total_revenue'])}</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Order Pending</td><td style='text-align:right;font-weight:600;font-size:13px;color:#d97706'>{$s['pending']} order</td></tr>";
        }

        $financeHtml = '';
        if (isset($data['finance'])) {
            $f = $data['finance'];
            $profitColor = $f['profit'] >= 0 ? '#16a34a' : '#dc2626';
            $profitLabel = $f['profit'] >= 0 ? '✅ Surplus' : '🔴 Defisit';
            $financeHtml = "
            <tr><td colspan='2' style='padding:12px 0 6px;font-weight:600;color:#1e40af;font-size:14px;border-top:1px solid #e2e8f0'>💰 Keuangan</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Pemasukan</td><td style='text-align:right;font-weight:600;font-size:13px;color:#16a34a'>{$fmt($f['income'])}</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Pengeluaran</td><td style='text-align:right;font-weight:600;font-size:13px;color:#dc2626'>{$fmt($f['expense'])}</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Laba Bersih</td><td style='text-align:right;font-weight:700;font-size:14px;color:{$profitColor}'>{$fmt($f['profit'])} {$profitLabel}</td></tr>";
        }

        $inventoryHtml = '';
        if (isset($data['inventory'])) {
            $i = $data['inventory'];
            $stockAlert = $i['low_stock'] > 0 ? "⚠️ {$i['low_stock']} produk stok rendah" : '✅ Stok aman';
            $inventoryHtml = "
            <tr><td colspan='2' style='padding:12px 0 6px;font-weight:600;color:#1e40af;font-size:14px;border-top:1px solid #e2e8f0'>📦 Inventori</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Total Produk Aktif</td><td style='text-align:right;font-weight:600;font-size:13px'>{$i['total_products']} produk</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Status Stok</td><td style='text-align:right;font-weight:600;font-size:13px'>{$stockAlert}</td></tr>";
        }

        $hrmHtml = '';
        if (isset($data['hrm'])) {
            $h = $data['hrm'];
            $hrmHtml = "
            <tr><td colspan='2' style='padding:12px 0 6px;font-weight:600;color:#1e40af;font-size:14px;border-top:1px solid #e2e8f0'>👥 Kehadiran Karyawan</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Hadir</td><td style='text-align:right;font-weight:600;font-size:13px;color:#16a34a'>{$h['present']} orang</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Absen</td><td style='text-align:right;font-weight:600;font-size:13px;color:#dc2626'>{$h['absent']} orang</td></tr>
            <tr><td style='padding:4px 0;color:#64748b;font-size:13px'>Terlambat</td><td style='text-align:right;font-weight:600;font-size:13px;color:#d97706'>{$h['late']} orang</td></tr>";
        }

        $year = now()->year;

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)">

        <!-- Header -->
        <tr><td style="background:linear-gradient(135deg,#1e40af,#3b82f6);padding:28px 32px">
          <p style="margin:0;color:#bfdbfe;font-size:12px;letter-spacing:1px;text-transform:uppercase">Qalcuity ERP</p>
          <h1 style="margin:8px 0 4px;color:#fff;font-size:22px;font-weight:700">📊 Ringkasan Bisnis</h1>
          <p style="margin:0;color:#93c5fd;font-size:14px">{$periodLabel}</p>
        </td></tr>

        <!-- Body -->
        <tr><td style="padding:28px 32px">
          <p style="margin:0 0 20px;color:#374151;font-size:14px">Halo, <strong>{$userName}</strong>! Berikut ringkasan bisnis Anda:</p>

          <table width="100%" cellpadding="0" cellspacing="0">
            {$salesHtml}
            {$financeHtml}
            {$inventoryHtml}
            {$hrmHtml}
          </table>

          <div style="margin-top:24px;padding:16px;background:#eff6ff;border-radius:10px;border-left:4px solid #3b82f6">
            <p style="margin:0;color:#1e40af;font-size:13px">💡 Untuk analisis lebih detail, buka Qalcuity AI dan ketik <em>"laporan lengkap bulan ini"</em>.</p>
          </div>
        </td></tr>

        <!-- CTA -->
        <tr><td style="padding:0 32px 28px;text-align:center">
          <a href="{$this->appUrl()}/dashboard" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600">Buka Dashboard →</a>
        </td></tr>

        <!-- Footer -->
        <tr><td style="padding:20px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center">
          <p style="margin:0;color:#94a3b8;font-size:12px">© {$year} Qalcuity ERP · Email ini dikirim otomatis oleh Qalcuity AI</p>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    private function appUrl(): string
    {
        return rtrim(config('app.url', 'http://localhost'), '/');
    }

    private function resolveDateRange(string $period): array
    {
        return match ($period) {
            'today' => [today(), today()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), today()],
        };
    }
}
