<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Tenant;
use Carbon\Carbon;

class DocumentGeneratorTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'generate_document',
                'description' => 'Generate dokumen bisnis siap cetak. Gunakan untuk: '
                    . '"buatkan surat penawaran untuk PT Maju", '
                    . '"buat kontrak kerja untuk Budi posisi kasir gaji 3 juta", '
                    . '"buat surat peringatan untuk karyawan X", '
                    . '"buat surat perjanjian kerjasama dengan PT Y", '
                    . '"buat memo internal tentang kebijakan baru", '
                    . '"buat surat keterangan kerja untuk Siti".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'doc_type'      => [
                            'type'        => 'string',
                            'description' => 'Jenis dokumen: penawaran (surat penawaran), kontrak_kerja, sp (surat peringatan), keterangan_kerja, perjanjian_kerjasama, memo, pkwt (kontrak waktu tertentu)',
                        ],
                        'recipient_name'=> ['type' => 'string', 'description' => 'Nama penerima/karyawan/perusahaan'],
                        'details'       => ['type' => 'string', 'description' => 'Detail tambahan: posisi, gaji, produk, harga, alasan SP, dll'],
                        'date'          => ['type' => 'string', 'description' => 'Tanggal dokumen (default: hari ini)'],
                        'valid_days'    => ['type' => 'integer', 'description' => 'Masa berlaku dalam hari (untuk penawaran, default: 14)'],
                    ],
                    'required' => ['doc_type', 'recipient_name'],
                ],
            ],
        ];
    }

    public function generateDocument(array $args): array
    {
        $tenant = Tenant::find($this->tenantId);
        $docType = $args['doc_type'] ?? 'memo';
        $recipient = $args['recipient_name'] ?? '-';
        $details = $args['details'] ?? '';
        $date = isset($args['date']) ? Carbon::parse($args['date']) : now();
        $dateStr = $date->locale('id')->isoFormat('D MMMM YYYY');

        $doc = match ($docType) {
            'penawaran'          => $this->buildPenawaran($tenant, $recipient, $details, $dateStr, $args),
            'kontrak_kerja'      => $this->buildKontrakKerja($tenant, $recipient, $details, $dateStr),
            'pkwt'               => $this->buildPkwt($tenant, $recipient, $details, $dateStr),
            'sp'                 => $this->buildSuratPeringatan($tenant, $recipient, $details, $dateStr),
            'keterangan_kerja'   => $this->buildKeteranganKerja($tenant, $recipient, $details, $dateStr),
            'perjanjian_kerjasama'=> $this->buildPerjanjianKerjasama($tenant, $recipient, $details, $dateStr),
            'memo'               => $this->buildMemo($tenant, $recipient, $details, $dateStr),
            default              => $this->buildMemo($tenant, $recipient, $details, $dateStr),
        };

        return [
            'status'   => 'success',
            'doc_type' => $docType,
            'document' => $doc,
            'message'  => 'Dokumen berhasil dibuat. Gunakan tombol Cetak untuk mencetak.',
        ];
    }

    private function buildPenawaran($tenant, string $recipient, string $details, string $date, array $args): array
    {
        $validDays = (int) ($args['valid_days'] ?? 14);
        $validUntil = now()->addDays($validDays)->locale('id')->isoFormat('D MMMM YYYY');

        return [
            'type' => 'Surat Penawaran',
            'from' => [
                'name'    => $tenant?->name ?? 'Perusahaan',
                'address' => $tenant?->address ?? '',
                'city'    => $tenant?->city ?? '',
                'phone'   => $tenant?->phone ?? '',
                'email'   => $tenant?->email ?? '',
                'npwp'    => $tenant?->npwp ?? '',
                'logo'    => $tenant?->logo ?? '',
                'stamp'   => $tenant?->stamp_image ?? '',
                'color'   => $tenant?->letter_head_color ?? '#1d4ed8',
            ],
            'to'   => ['name' => $recipient, 'address' => ''],
            'date' => $date,
            'subject' => 'Penawaran Produk/Jasa',
            'body' => "Dengan hormat,\n\nBersama surat ini, kami dari {$tenant?->name} dengan senang hati menawarkan produk/jasa kami kepada {$recipient}.\n\n{$details}\n\nPenawaran ini berlaku hingga {$validUntil}. Kami berharap dapat menjalin kerjasama yang saling menguntungkan.\n\nUntuk informasi lebih lanjut, silakan hubungi kami.",
            'closing' => 'Hormat kami,',
            'signer'  => $tenant?->name ?? 'Pimpinan',
            'position'=> 'Direktur',
        ];
    }

    private function tenantFrom($tenant): array
    {
        return [
            'name'    => $tenant?->name ?? 'Perusahaan',
            'address' => $tenant?->address ?? '',
            'city'    => $tenant?->city ?? '',
            'phone'   => $tenant?->phone ?? '',
            'email'   => $tenant?->email ?? '',
            'npwp'    => $tenant?->npwp ?? '',
            'logo'    => $tenant?->logo ?? '',
            'stamp'   => $tenant?->stamp_image ?? '',
            'color'   => $tenant?->letter_head_color ?? '#1d4ed8',
            'tagline' => $tenant?->tagline ?? '',
        ];
    }

    private function buildKontrakKerja($tenant, string $employeeName, string $details, string $date): array
    {
        // Parse details: "posisi kasir gaji 3 juta"
        preg_match('/posisi\s+([^,\n]+)/i', $details, $posMatch);
        preg_match('/gaji\s+([\d\s]+(?:juta|ribu)?)/i', $details, $gajiMatch);
        $posisi = trim($posMatch[1] ?? 'Karyawan');
        $gaji   = trim($gajiMatch[1] ?? '-');

        return [
            'type' => 'Kontrak Kerja',
            'from' => $this->tenantFrom($tenant),
            'to'   => ['name' => $employeeName, 'address' => ''],
            'date' => $date,
            'subject' => "Perjanjian Kerja — {$posisi}",
            'body' => "Yang bertanda tangan di bawah ini:\n\n**Pihak Pertama (Perusahaan):**\nNama: {$tenant?->name}\nAlamat: {$tenant?->address}\n\n**Pihak Kedua (Karyawan):**\nNama: {$employeeName}\n\nDengan ini sepakat mengadakan Perjanjian Kerja dengan ketentuan:\n\n**Pasal 1 — Jabatan dan Tugas**\nPihak Kedua diterima bekerja sebagai **{$posisi}** dan wajib melaksanakan tugas sesuai jabatan.\n\n**Pasal 2 — Kompensasi**\nPihak Pertama memberikan gaji pokok sebesar **{$gaji}** per bulan, dibayarkan setiap akhir bulan.\n\n**Pasal 3 — Jam Kerja**\nJam kerja mengikuti ketentuan perusahaan yang berlaku.\n\n**Pasal 4 — Kewajiban Karyawan**\nPihak Kedua wajib menjaga kerahasiaan perusahaan dan mematuhi peraturan yang berlaku.\n\n**Pasal 5 — Pemutusan Hubungan Kerja**\nPemutusan kerja dilakukan sesuai ketentuan UU Ketenagakerjaan yang berlaku.\n\nDemikian perjanjian ini dibuat dengan itikad baik.",
            'closing' => 'Menyetujui,',
            'signer'  => $tenant?->name ?? 'Pimpinan',
            'position'=> 'Direktur',
        ];
    }

    private function buildPkwt($tenant, string $employeeName, string $details, string $date): array
    {
        preg_match('/(\d+)\s*bulan/i', $details, $durMatch);
        $durasi = $durMatch[1] ?? '3';

        return [
            'type' => 'PKWT (Perjanjian Kerja Waktu Tertentu)',
            'from' => $this->tenantFrom($tenant),
            'to'   => ['name' => $employeeName, 'address' => ''],
            'date' => $date,
            'subject' => "PKWT — {$employeeName}",
            'body' => "Perjanjian Kerja Waktu Tertentu (PKWT) ini dibuat antara:\n\n**{$tenant?->name}** (Perusahaan) dan **{$employeeName}** (Karyawan).\n\nKaryawan dipekerjakan untuk jangka waktu **{$durasi} bulan** terhitung sejak tanggal penandatanganan.\n\n{$details}\n\nSetelah berakhirnya masa kontrak, perjanjian ini dapat diperpanjang berdasarkan kesepakatan kedua belah pihak.",
            'closing' => 'Menyetujui,',
            'signer'  => $tenant?->name ?? 'Pimpinan',
            'position'=> 'Direktur',
        ];
    }

    private function buildSuratPeringatan($tenant, string $employeeName, string $details, string $date): array
    {
        return [
            'type' => 'Surat Peringatan',
            'from' => $this->tenantFrom($tenant),
            'to'   => ['name' => $employeeName, 'address' => ''],
            'date' => $date,
            'subject' => "Surat Peringatan — {$employeeName}",
            'body' => "Dengan hormat,\n\nMelalui surat ini, manajemen {$tenant?->name} memberikan peringatan kepada:\n\n**Nama:** {$employeeName}\n\n**Perihal Pelanggaran:**\n{$details}\n\nKami berharap Saudara/i dapat memperbaiki perilaku dan kinerja sesuai standar perusahaan. Apabila pelanggaran terulang, perusahaan berhak mengambil tindakan lebih lanjut sesuai peraturan yang berlaku.\n\nDemikian surat peringatan ini dibuat untuk diperhatikan.",
            'closing' => 'Hormat kami,',
            'signer'  => 'HRD / Pimpinan',
            'position'=> $tenant?->name ?? 'Perusahaan',
        ];
    }

    private function buildKeteranganKerja($tenant, string $employeeName, string $details, string $date): array
    {
        preg_match('/posisi\s+([^,\n]+)/i', $details, $posMatch);
        $posisi = trim($posMatch[1] ?? 'Karyawan');

        return [
            'type' => 'Surat Keterangan Kerja',
            'from' => $this->tenantFrom($tenant),
            'to'   => ['name' => 'Yang Berkepentingan', 'address' => ''],
            'date' => $date,
            'subject' => "Surat Keterangan Kerja — {$employeeName}",
            'body' => "Yang bertanda tangan di bawah ini menerangkan bahwa:\n\n**Nama:** {$employeeName}\n**Jabatan:** {$posisi}\n**Perusahaan:** {$tenant?->name}\n\nYang bersangkutan adalah benar karyawan aktif di {$tenant?->name} dan memiliki rekam jejak kerja yang baik.\n\nSurat keterangan ini dibuat untuk keperluan yang bersangkutan dan dapat digunakan sebagaimana mestinya.",
            'closing' => 'Hormat kami,',
            'signer'  => 'HRD / Pimpinan',
            'position'=> $tenant?->name ?? 'Perusahaan',
        ];
    }

    private function buildPerjanjianKerjasama($tenant, string $partnerName, string $details, string $date): array
    {
        return [
            'type' => 'Perjanjian Kerjasama',
            'from' => $this->tenantFrom($tenant),
            'to'   => ['name' => $partnerName, 'address' => ''],
            'date' => $date,
            'subject' => "Perjanjian Kerjasama antara {$tenant?->name} dan {$partnerName}",
            'body' => "Perjanjian Kerjasama ini dibuat oleh:\n\n**Pihak Pertama:** {$tenant?->name}\n**Pihak Kedua:** {$partnerName}\n\n**Ruang Lingkup Kerjasama:**\n{$details}\n\n**Pasal 1 — Tujuan**\nKedua pihak sepakat untuk menjalin kerjasama yang saling menguntungkan.\n\n**Pasal 2 — Hak dan Kewajiban**\nMasing-masing pihak bertanggung jawab atas kewajiban yang telah disepakati.\n\n**Pasal 3 — Kerahasiaan**\nKedua pihak wajib menjaga kerahasiaan informasi yang diperoleh selama kerjasama.\n\n**Pasal 4 — Penyelesaian Sengketa**\nSengketa diselesaikan secara musyawarah mufakat.\n\nDemikian perjanjian ini dibuat dengan itikad baik.",
            'closing' => 'Menyetujui,',
            'signer'  => $tenant?->name ?? 'Pimpinan',
            'position'=> 'Direktur',
        ];
    }

    private function buildMemo($tenant, string $to, string $details, string $date): array
    {
        return [
            'type' => 'Memo Internal',
            'from' => $this->tenantFrom($tenant),
            'to'   => ['name' => $to, 'address' => ''],
            'date' => $date,
            'subject' => 'Memo Internal',
            'body' => $details ?: 'Isi memo di sini.',
            'closing' => 'Hormat kami,',
            'signer'  => 'Manajemen',
            'position'=> $tenant?->name ?? '',
        ];
    }
}
