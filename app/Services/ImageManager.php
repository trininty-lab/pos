<?php

namespace App\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageManager
{
    public static function allTypes(): array
    {
        return config('media.image_types', []);
    }

    public static function forType(string $type): array
    {
        $key = self::normalizeType($type);
        $types = self::allTypes();

        if (! isset($types[$key])) {
            $fallback = self::defaultConfig($key);
            $types[$key] = $fallback;
            config()->set('media.image_types.'.$key, $fallback);
        }

        return $types[$key];
    }

    public static function typeKey(string $type): string
    {
        return self::normalizeType($type);
    }

    public static function normalizeType(string $type): string
    {
        $normalized = strtoupper(trim((string) $type));

        if ($normalized === '') {
            throw new InvalidArgumentException('Image type is required.');
        }

        return str_replace(['-', ' '], '_', $normalized);
    }

    public static function defaultConfig(string $type): array
    {
        $slug = Str::slug(strtolower(str_replace('_', ' ', $type)), '-');

        return [
            'label' => ucfirst(str_replace('-', ' ', $slug)),
            'allowed_formats' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_file_size' => 5 * 1024 * 1024,
            'min_dimensions' => ['width' => 200, 'height' => 200],
            'max_dimensions' => ['width' => 4000, 'height' => 4000],
            'aspect_ratio' => 'landscape',
            'crop_mode' => 'fit',
            'output_formats' => ['webp'],
            'compression_quality' => 80,
            'thumbnail_sizes' => [200],
            'preview_sizes' => [400],
            'keep_original' => false,
            'storage_path' => $slug,
            'allow_transparent' => false,
            'multiple' => false,
            'replacement_supported' => true,
        ];
    }
}
