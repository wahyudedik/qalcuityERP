<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'doc_type', 'html_content', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function docTypeLabel(string $type): string
    {
        return match ($type) {
            'invoice' => 'Invoice',
            'po' => 'Purchase Order',
            'quotation' => 'Penawaran (Quotation)',
            'letter' => 'Surat Umum',
            'memo' => 'Memo Internal',
            default => ucfirst($type),
        };
    }
}
