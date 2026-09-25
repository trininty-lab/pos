<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'business_id',
        'status',
        'code',
        'address',
    ];

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'store_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
