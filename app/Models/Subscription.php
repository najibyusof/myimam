<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'is_trial',
        'start_date',
        'end_date',
        'trial_ends_at',
        'auto_renew',
        'reminder_sent_at',
        'renewal_of_id',
    ];

    protected function casts(): array
    {
        return [
            'is_trial' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'trial_ends_at' => 'datetime',
            'auto_renew' => 'boolean',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'tenant_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
