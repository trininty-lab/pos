<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'cloudinary_bytes' => 'integer',
        'cloudinary_width' => 'integer',
        'cloudinary_height' => 'integer',
    ];

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'image', 'name', 'description',
        'cloudinary_public_id',
        'cloudinary_version',
        'cloudinary_format',
        'cloudinary_resource_type',
        'cloudinary_bytes',
        'cloudinary_width',
        'cloudinary_height',
    ];

    /**
     * products
     *
     * @return void
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * image
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->cloudinary_public_id) {
                    return app(\App\Services\ImageService::class)->getImageUrl($this->cloudinary_public_id);
                }

                if (! $value) {
                    return null;
                }

                return $value === 'default.jpg'
                    ? asset('/images/icon-512.png')
                    : (filter_var($value, FILTER_VALIDATE_URL)
                        ? $value
                        : asset('/storage/categories/'.ltrim($value, '/')));
            },
        );
    }
}
