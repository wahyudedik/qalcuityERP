<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationDocument extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'registration_id',
        'document_name',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'description',
    ];

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'certificate' => 'Certificate',
            'formula' => 'Formula Document',
            'label' => 'Label Design',
            'test_report' => 'Test Report',
            'sds' => 'Safety Data Sheet',
            'other' => 'Other Document',
            default => ucfirst(str_replace('_', ' ', $this->document_type))
        };
    }

    // Get file size in MB
    public function getFileSizeMbAttribute(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        return number_format($this->file_size / 1024, 2).' MB';
    }

    // Relationship
    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProductRegistration::class, 'registration_id');
    }
}
