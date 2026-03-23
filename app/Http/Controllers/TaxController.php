<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index()
    {
        $taxes = TaxRate::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        return view('settings.taxes', compact('taxes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20',
            'type'          => 'required|in:percentage,fixed',
            'tax_type'      => 'required|in:ppn,pph21,pph23,pph4ayat2,custom',
            'rate'          => 'required|numeric|min:0|max:100',
            'is_withholding'=> 'boolean',
            'account_code'  => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        $tid = $this->tenantId();

        if (TaxRate::where('tenant_id', $tid)->where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'Kode pajak sudah digunakan.'])->withInput();
        }

        $data['is_withholding'] = $request->boolean('is_withholding');
        TaxRate::create(['tenant_id' => $tid, 'is_active' => true] + $data);

        return back()->with('success', "Tarif pajak {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, TaxRate $tax)
    {
        abort_unless($tax->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20',
            'type'          => 'required|in:percentage,fixed',
            'tax_type'      => 'required|in:ppn,pph21,pph23,pph4ayat2,custom',
            'rate'          => 'required|numeric|min:0|max:100',
            'is_withholding'=> 'boolean',
            'account_code'  => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        $data['is_active']     = $request->boolean('is_active');
        $data['is_withholding']= $request->boolean('is_withholding');
        $tax->update($data);

        return back()->with('success', "Tarif pajak {$tax->name} berhasil diperbarui.");
    }

    public function destroy(TaxRate $tax)
    {
        abort_unless($tax->tenant_id === $this->tenantId(), 403);
        $tax->delete();
        return back()->with('success', 'Tarif pajak berhasil dihapus.');
    }

    /**
     * Export e-Faktur CSV (format DJP Indonesia)
     * Kolom: FK, KD_JENIS_TRANSAKSI, FG_PENGGANTI, NOMOR_FAKTUR, MASA_PAJAK,
     *        TAHUN_PAJAK, TANGGAL_FAKTUR, NPWP, NAMA, ALAMAT_LENGKAP,
     *        JUMLAH_DPP, JUMLAH_PPN, JUMLAH_PPNBM, IS_CREDITABLE
     */
    public function exportEfaktur(Request $request)
    {
        $tid  = $this->tenantId();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $invoices = \App\Models\Invoice::with(['customer', 'taxRate'])
            ->where('tenant_id', $tid)
            ->whereHas('taxRate', fn($q) => $q->where('tax_type', 'ppn'))
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->get();

        $filename = 'efaktur_' . str_replace('-', '', $from) . '_' . str_replace('-', '', $to) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $handle = fopen('php://output', 'w');

            // Header row e-Faktur DJP
            fputcsv($handle, [
                'FK', 'KD_JENIS_TRANSAKSI', 'FG_PENGGANTI', 'NOMOR_FAKTUR',
                'MASA_PAJAK', 'TAHUN_PAJAK', 'TANGGAL_FAKTUR',
                'NPWP', 'NAMA', 'ALAMAT_LENGKAP',
                'JUMLAH_DPP', 'JUMLAH_PPN', 'JUMLAH_PPNBM', 'IS_CREDITABLE',
            ]);

            foreach ($invoices as $inv) {
                $dpp = (float) $inv->subtotal_amount;
                $ppn = (float) $inv->tax_amount;
                $date = $inv->created_at->format('d/m/Y');
                $masa = $inv->created_at->month;
                $tahun = $inv->created_at->year;

                fputcsv($handle, [
                    'FK',           // Faktur Keluaran
                    '01',           // Penyerahan BKP/JKP
                    '0',            // Bukan pengganti
                    $inv->number,   // Nomor faktur
                    $masa,
                    $tahun,
                    $date,
                    $inv->customer?->npwp ?? '000000000000000',
                    $inv->customer?->name ?? '',
                    $inv->customer?->address ?? '',
                    (int) $dpp,
                    (int) $ppn,
                    0,              // PPNBM
                    1,              // Dapat dikreditkan
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
