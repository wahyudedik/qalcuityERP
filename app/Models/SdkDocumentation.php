<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SdkDocumentation extends Model
{
    use HasFactory;

    protected $table = 'sdk_documentation';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'order',
        'is_published',
        'code_examples',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_published' => 'boolean',
        'code_examples' => 'array',
    ];
}
