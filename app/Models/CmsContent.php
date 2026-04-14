<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsContent extends Model
{
    use HasFactory;

    protected $table = 'contents';

    protected $fillable = [
        'section_id',
        'content_key',
        'content_text',
        'content_json',
        'image_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CmsSection::class, 'section_id');
    }
}
