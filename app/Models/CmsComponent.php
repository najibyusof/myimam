<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsComponent extends Model
{
    use HasFactory;

    protected $table = 'cms_components';

    protected $fillable = [
        'name',
        'type',
        'schema_json',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
        ];
    }
}
