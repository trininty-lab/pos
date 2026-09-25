<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'image_type',
        'entity_type',
        'entity_id',
        'business_id',
        'store_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'business_id' => 'integer',
        'store_id' => 'integer',
        'created_by' => 'integer',
        'metadata' => 'array',
        'entity_id' => 'integer',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function url(?string $variant = null): string
    {
        $path = $this->path;

        if ($path === null || $path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
