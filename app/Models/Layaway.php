<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layaway extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'user_id',
        'status',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'notes',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
        'released_at',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
        'balance_amount' => 'integer',
        'cancelled_at' => 'datetime',
        'released_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $layaway): void {
            if (empty($layaway->reference)) {
                $next = self::query()->max('id') + 1;
                $layaway->reference = 'LAY-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LayawayItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LayawayPayment::class)->orderBy('paid_at');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
