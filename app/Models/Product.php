<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $appends = ['image_variants'];

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'buy_price' => 'integer',
        'sell_price' => 'integer',
        'minimum_sell_price' => 'integer',
        'maximum_sell_price' => 'integer',
        'stock' => 'integer',
        'tax_rate' => 'decimal:2',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'is_composite' => 'boolean',
        'cloudinary_bytes' => 'integer',
        'cloudinary_width' => 'integer',
        'cloudinary_height' => 'integer',
        'image_crop' => 'array',
    ];

    protected $fillable = [
        'image',
        'barcode',
        'sku',
        'title',
        'description',
        'buy_price',
        'sell_price',
        'minimum_sell_price',
        'maximum_sell_price',
        'category_id',
        'stock',
        'tax_type',
        'tax_rate',
        'min_stock',
        'max_stock',
        'is_composite',
        'cloudinary_public_id',
        'cloudinary_version',
        'cloudinary_format',
        'cloudinary_resource_type',
        'cloudinary_bytes',
        'cloudinary_width',
        'cloudinary_height',
        'image_crop',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class)
            ->withPivot('stock')
            ->using(ProductWarehouse::class)
            ->withTimestamps();
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'product_units')
            ->withPivot(['is_base', 'conversion_factor', 'buy_price', 'sell_price', 'barcode', 'sku_suffix'])
            ->using(ProductUnit::class)
            ->withTimestamps();
    }

    public function baseUnit(): ?Unit
    {
        return $this->units()->wherePivot('is_base', true)->first();
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'composite_product_items', 'composite_product_id', 'component_product_id')
            ->withPivot('qty')
            ->withTimestamps();
    }

    public function compositeOf(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'composite_product_items', 'component_product_id', 'composite_product_id');
    }

    public function compositeStock(): int
    {
        if (! $this->is_composite) {
            return $this->stockTotal();
        }
        $minStock = null;
        foreach ($this->components as $component) {
            $available = (int) floor($component->stockTotal() / max(1, (float) $component->pivot->qty));
            $minStock = $minStock === null ? $available : min($minStock, $available);
        }

        return $minStock ?? 0;
    }

    public function stockOpnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function salesReturnItems()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class);
    }

    public function stockTotal(): int
    {
        return (int) $this->warehouses()->sum('product_warehouse.stock');
    }

    public function isLowStock(?int $warehouseId = null): bool
    {
        if ($this->min_stock <= 0) {
            return false;
        }
        $stock = $warehouseId
            ? (int) ($this->warehouses()->where('warehouse_id', $warehouseId)->first()?->pivot->stock ?? 0)
            : $this->stockTotal();

        return $stock <= $this->min_stock;
    }

    public function suggestedOrderQty(): int
    {
        if ($this->max_stock <= 0 || $this->min_stock <= 0) {
            return 0;
        }

        return max(0, $this->max_stock - $this->stockTotal());
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) {
                    return null;
                }

                if ($value === 'default.jpg') {
                    return asset('/images/icon-512.png');
                }

                if ($this->cloudinary_public_id) {
                    return app(\App\Services\ImageService::class)->getImageUrl(
                        $this->cloudinary_public_id,
                        800,
                        800,
                        $this->image_crop
                    );
                }

                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

                return asset('/storage/products/'.ltrim($value, '/'));
            },
        );
    }

    protected function imageVariants(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->cloudinary_public_id) {
                    $url = $this->image;

                    return ['thumb' => $url, 'card' => $url, 'display' => $url, 'large' => $url];
                }

                return app(\App\Services\ImageService::class)->getProductVariants(
                    $this->cloudinary_public_id,
                    $this->image_crop
                );
            },
        );
    }
}
