<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'tenant_id', 'uploaded_by', 'title', 'file_name', 'file_path',
        'file_type', 'file_size', 'category', 'related_type', 'related_id',
        'description', 'tags',
    ];

    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function related() { return $this->morphTo(); }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
