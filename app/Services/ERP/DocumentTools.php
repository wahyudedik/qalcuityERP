<?php

namespace App\Services\ERP;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'list_documents',
                'description' => 'Tampilkan daftar dokumen yang tersimpan, bisa filter per kategori atau transaksi.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'category' => ['type' => 'string', 'description' => 'contract, invoice, po, so, hr, other (opsional)'],
                        'search' => ['type' => 'string', 'description' => 'Kata kunci judul/deskripsi (opsional)'],
                        'related_type' => ['type' => 'string', 'description' => 'Tipe relasi: PurchaseOrder, SalesOrder, Employee (opsional)'],
                        'related_id' => ['type' => 'integer', 'description' => 'ID relasi (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'get_document_info',
                'description' => 'Lihat detail informasi dokumen tertentu.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Judul dokumen'],
                    ],
                    'required' => ['title'],
                ],
            ],
            [
                'name' => 'delete_document',
                'description' => 'Hapus dokumen dari sistem.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Judul dokumen yang akan dihapus'],
                    ],
                    'required' => ['title'],
                ],
            ],
        ];
    }

    public function listDocuments(array $args): array
    {
        $query = Document::where('tenant_id', $this->tenantId);

        if (! empty($args['category'])) {
            $query->where('category', $args['category']);
        }
        if (! empty($args['related_type'])) {
            $query->where('related_type', 'like', "%{$args['related_type']}%");
        }
        if (! empty($args['related_id'])) {
            $query->where('related_id', $args['related_id']);
        }
        if (! empty($args['search'])) {
            $kw = $args['search'];
            $query->where(fn ($q) => $q->where('title', 'like', "%{$kw}%")->orWhere('description', 'like', "%{$kw}%")->orWhere('tags', 'like', "%{$kw}%"));
        }

        $docs = $query->orderByDesc('created_at')->get();

        if ($docs->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada dokumen yang ditemukan.'];
        }

        return [
            'status' => 'success',
            'total' => $docs->count(),
            'data' => $docs->map(fn ($d) => [
                'judul' => $d->title,
                'kategori' => $d->category ?? '-',
                'tipe_file' => $d->file_type ?? '-',
                'ukuran' => $d->file_size_human,
                'deskripsi' => $d->description ?? '-',
                'tags' => $d->tags ?? '-',
                'diunggah' => $d->created_at->format('d M Y'),
            ])->toArray(),
        ];
    }

    public function getDocumentInfo(array $args): array
    {
        $doc = Document::where('tenant_id', $this->tenantId)
            ->where('title', 'like', "%{$args['title']}%")
            ->first();

        if (! $doc) {
            return ['status' => 'not_found', 'message' => "Dokumen '{$args['title']}' tidak ditemukan."];
        }

        return [
            'status' => 'success',
            'data' => [
                'judul' => $doc->title,
                'file' => $doc->file_name,
                'tipe' => $doc->file_type,
                'ukuran' => $doc->file_size_human,
                'kategori' => $doc->category,
                'deskripsi' => $doc->description,
                'tags' => $doc->tags,
                'diunggah_oleh' => $doc->uploader?->name ?? '-',
                'tanggal' => $doc->created_at->format('d M Y H:i'),
            ],
        ];
    }

    public function deleteDocument(array $args): array
    {
        $doc = Document::where('tenant_id', $this->tenantId)
            ->where('title', 'like', "%{$args['title']}%")
            ->first();

        if (! $doc) {
            return ['status' => 'not_found', 'message' => "Dokumen '{$args['title']}' tidak ditemukan."];
        }

        // Delete file from storage
        if ($doc->file_path && Storage::exists($doc->file_path)) {
            Storage::delete($doc->file_path);
        }

        $title = $doc->title;
        $doc->delete();

        return ['status' => 'success', 'message' => "Dokumen **{$title}** berhasil dihapus."];
    }
}
