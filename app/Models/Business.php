<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (self $business): void {
            if (! filled($business->owner_id)) {
                return;
            }

            $business->owner()->update(['business_id' => $business->id]);
        });
    }

    protected $fillable = [
        'name',
        'registration_number',
        'tax_pin',
        'phone',
        'email',
        'address',
        'country',
        'currency',
        'status',
        'owner_id',
        'last_activity_at',
        'suspended_at',
        'archived_at',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'last_activity_at' => 'datetime',
        'suspended_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
