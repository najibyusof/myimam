<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'amount',
        'status',
        'gateway',
        'reference_id',
        'invoice_no',
        'invoice_path',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'tenant_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
