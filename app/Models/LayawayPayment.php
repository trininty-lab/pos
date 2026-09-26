<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayawayPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'layaway_id',
        'amount',
        'payment_method',
        'payment_reference',
        'user_id',
        'paid_at',
        'balance_after_payment',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after_payment' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function layaway(): BelongsTo
    {
        return $this->belongsTo(Layaway::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
