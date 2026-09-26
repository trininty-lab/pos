<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockMutationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FashionCatalogSeeder extends Seeder
{
    private const CATEGORY_GROUPS = [
        "Men's Clothing" => ['T-Shirts', 'Polo Shirts', 'Shirts', 'Jeans', 'Trousers', 'Shorts', 'Jackets', 'Hoodies & Sweatshirts', 'Suits & Blazers', 'Sweaters', 'Tracksuits', 'Vests'],
        "Women's Clothing" => ['Dresses', 'Tops', 'Blouses', 'T-Shirts', 'Skirts', 'Jeans', 'Trousers', 'Leggings', 'Jumpsuits', 'Jackets', 'Sweaters', 'Hoodies', 'Suits & Blazers'],
        "Children's Clothing" => ["Boys' Clothing", "Girls' Clothing", "Children's T-Shirts", "Children's Dresses", "Children's Trousers", "Children's Shorts", "Children's Jackets", 'School Wear'],
        'Footwear' => ["Men's Shoes", "Women's Shoes", "Children's Shoes", 'Sneakers', 'Sandals', 'Boots', 'Formal Shoes', 'Slippers'],
        'Fashion Accessories' => ['Belts', 'Caps & Hats', 'Bags', 'Wallets', 'Scarves', 'Sunglasses', 'Socks', 'Ties'],
        'Innerwear' => ["Men's Underwear", "Women's Innerwear", 'Vests', 'Bras', 'Socks'],
        'Sportswear' => ['Sports T-Shirts', 'Sports Shorts', 'Tracksuits', 'Training Pants', 'Sports Jerseys', 'Sports Jackets'],
        'Traditional & Occasion Wear' => ['African Dresses', 'Ankara Wear', 'Kitenge Wear', 'Shirts', 'Ceremonial Wear', 'Wedding/Occasion Wear'],
    ];

    private const COLOURS = ['Black', 'Navy Blue', 'White', 'Charcoal', 'Olive', 'Burgundy', 'Cream', 'Royal Blue'];

    public function run(): void
    {
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'PUSAT'],
            ['name' => 'FAY COLLECTION Main Store', 'type' => 'main', 'is_active' => true, 'sort_order' => 0]
        );

        $categories = $this->seedCategories();
        $created = 0;

        foreach ($categories as $category) {
            foreach ($this->productsForCategory($category) as $productData) {
                $product = Product::firstOrCreate(
                    ['sku' => $productData['sku']],
                    $productData + ['category_id' => $category->id]
                );

                if ($product->wasRecentlyCreated) {
                    $warehouse->products()->syncWithoutDetaching([
                        $product->id => ['stock' => $productData['stock']],
                    ]);
                    app(StockMutationService::class)->recordInitialStock($product, null, $warehouse->id);
                    $created++;
                }
            }
        }

        $this->command?->info("Fashion catalog ready: {$categories->count()} categories, {$created} new products.");
    }

    private function seedCategories()
    {
        $categories = collect();

        foreach (self::CATEGORY_GROUPS as $group => $children) {
            $categories->push(Category::firstOrCreate(
                ['name' => $group],
                ['image' => 'default.jpg', 'description' => "{$group} collection at FAY COLLECTION."]
            ));

            foreach ($children as $child) {
                $categories->push(Category::firstOrCreate(
                    ['name' => $child],
                    ['image' => 'default.jpg', 'description' => "{$child} in the {$group} collection."]
                ));
            }
        }

        return $categories;
    }

    private function productsForCategory(Category $category): array
    {
        $categoryName = $category->name;
        $slug = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $categoryName)))
            ->filter()
            ->map(fn ($word) => strtoupper($word[0]))
            ->implode('');
        $slug = substr($slug ?: 'FAS', 0, 4).str_pad((string) $category->id, 3, '0', STR_PAD_LEFT);
        $baseNames = $this->nameParts($categoryName);
        $products = [];

        foreach ($baseNames as $index => $name) {
            $number = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $stock = $this->stockFor($categoryName, $index);
            $cost = $this->costFor($categoryName, $index);
            $sell = $this->sellFor($cost, $index);
            $colour = self::COLOURS[($index + strlen($categoryName)) % count(self::COLOURS)];
            $size = $this->sizeFor($categoryName, $index);

            $products[] = [
                'image' => 'default.jpg',
                'barcode' => '616'.str_pad((string) (abs(crc32($categoryName.'-'.$index)) % 1000000000), 9, '0', STR_PAD_LEFT),
                'sku' => "{$slug}-{$number}",
                'title' => $name,
                'description' => "{$name}. {$colour} colour, {$size} size. Kenyan retail fashion stock from FAY COLLECTION.",
                'buy_price' => $cost,
                'sell_price' => $sell,
                'minimum_sell_price' => $sell,
                'maximum_sell_price' => (int) round($sell * 1.08),
                'stock' => $stock,
                'tax_type' => 'exclusive',
                'tax_rate' => 0,
                'min_stock' => $this->reorderFor($categoryName),
                'max_stock' => max($stock + 20, $this->reorderFor($categoryName) * 4),
                'is_composite' => false,
            ];
        }

        return $products;
    }

    private function nameParts(string $category): array
    {
        return match ($category) {
            'T-Shirts' => ["Men's Classic Cotton Crew Neck T-Shirt", "Men's Heavyweight Graphic Print T-Shirt"],
            'Polo Shirts' => ["Men's Slim Fit Pique Polo Shirt", "Men's Contrast Collar Polo Shirt"],
            'Shirts' => ["Men's Long Sleeve Oxford Shirt", "Men's Short Sleeve Linen Blend Shirt"],
            'Jeans' => ["Men's Straight Fit Indigo Jeans", "Men's Tapered Stretch Denim Jeans"],
            'Trousers' => ["Men's Khaki Chino Trousers", "Men's Tailored Office Trousers"],
            'Dresses' => ["Women's Floral Midi Dress", "Women's Belted Shirt Dress"],
            'Blouses' => ["Women's Casual Satin Blouse", "Women's Printed Peplum Blouse"],
            'Skirts' => ["Women's Pleated Midi Skirt", "Women's A-Line Denim Skirt"],
            'Sneakers' => ["Unisex Court Trainer Sneakers", "Men's Running Mesh Sneakers"],
            'Formal Shoes' => ["Men's Leather Derby Formal Shoes", "Women's Pointed Court Shoes"],
            'African Dresses' => ["African Print Maxi Dress", "African Print Wrap Dress"],
            'Ankara Wear' => ["Ankara Peplum Top and Skirt Set", "Ankara Two-Piece Occasion Set"],
            'Kitenge Wear' => ["Kitenge Tailored Peplum Dress", "Kitenge Print Shirt"],
            'Bags' => ["Canvas Crossbody Shoulder Bag", "Structured Faux Leather Tote Bag"],
            'Wallets' => ["Genuine Leather Bifold Wallet", "Compact Zip Card Wallet"],
            'Belts' => ["Classic Leather Pin Buckle Belt", "Reversible Canvas Web Belt"],
            'Socks' => ["Cotton Ankle Socks Three Pack", "Ribbed Sports Crew Socks"],
            default => [$this->titleCase($category).' Everyday Collection', $this->titleCase($category).' Premium Collection'],
        };
    }

    private function titleCase(string $value): string
    {
        return Str::of($value)->replace("'", '')->title()->toString();
    }

    private function sizeFor(string $category, int $index): string
    {
        if (str_contains($category, 'Children') || str_contains($category, "Boys") || str_contains($category, "Girls") || $category === 'School Wear') {
            return ['6-7 Years', '8-9 Years'][$index];
        }

        return ['M', 'L'][$index];
    }

    private function stockFor(string $category, int $index): int
    {
        if (in_array($category, ['Suits & Blazers', 'Ceremonial Wear', 'Wedding/Occasion Wear'], true)) {
            return $index === 1 ? 0 : 5;
        }

        if (in_array($category, ['T-Shirts', 'Tops', 'Socks', 'Belts'], true)) {
            return $index === 1 ? 8 : 32;
        }

        return [18, 12][$index];
    }

    private function reorderFor(string $category): int
    {
        return in_array($category, ['T-Shirts', 'Tops', 'Socks', 'Belts'], true) ? 12 : 5;
    }

    private function costFor(string $category, int $index): int
    {
        $base = match (true) {
            str_contains($category, 'Suits') || str_contains($category, 'Wedding') => 6500,
            str_contains($category, 'Shoes') || $category === 'Sneakers' => 1800,
            str_contains($category, 'Dresses') || str_contains($category, 'Ankara') || str_contains($category, 'Kitenge') => 1400,
            str_contains($category, 'Accessories') || in_array($category, ['Belts', 'Socks', 'Wallets'], true) => 250,
            default => 800,
        };

        return $base + ($index * 250);
    }

    private function sellFor(int $cost, int $index): int
    {
        return $cost + ($index === 0 ? 650 : 1100);
    }
}
