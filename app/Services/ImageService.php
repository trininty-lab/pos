<?php

namespace App\Services;

use App\Models\Product;
use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Transformation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class ImageService
{
    private const MAX_KILOBYTES = 5120;

    private const MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MIN_DIMENSION = 300;

    private const MAX_PIXELS = 40000000;

    public function __construct(private readonly Cloudinary $cloudinary) {}

    public function uploadImage(UploadedFile $file, string $folder): ImageResult
    {
        $this->validateFile($file);
        $this->validateFolder($folder);

        return $this->uploadRemoteImage($file, $folder);
    }

    public function replaceImage(
        UploadedFile $file,
        string $folder,
        ?string $oldPublicId = null,
    ): ImageResult {
        $result = $this->uploadImage($file, $folder);

        if ($oldPublicId && $oldPublicId !== $result->publicId) {
            try {
                $this->deleteImage($oldPublicId);
            } catch (Throwable $exception) {
                $this->tryDelete($result->publicId);

                throw new RuntimeException('Image replacement failed while removing the previous asset.', 0, $exception);
            }
        }

        return $result;
    }

    public function deleteImage(string $publicId): void
    {
        if (! $this->isSafePublicId($publicId)) {
            throw new RuntimeException('Invalid Cloudinary public ID.');
        }

        $response = $this->cloudinary->uploadApi()->destroy($publicId, [
            'resource_type' => 'image',
            'type' => 'upload',
            'invalidate' => true,
        ]);

        $result = $response['result'] ?? null;
        if ($result !== null && ! in_array($result, ['ok', 'not found'], true)) {
            throw new RuntimeException('Cloudinary did not confirm image deletion.');
        }
    }

    public function attachProductImage(Product $product, UploadedFile $file, ?array $crop = null): void
    {
        $uploaded = $this->uploadImage($file, 'products/'.$product->id);

        try {
            $this->saveProductImage($product, $uploaded, $this->normalizeCrop($crop, $uploaded));
        } catch (Throwable $exception) {
            $this->tryDelete($uploaded->publicId);
            throw new RuntimeException('Product image could not be associated.', 0, $exception);
        }
    }

    public function replaceProductImage(Product $product, UploadedFile $file, ?array $crop = null): void
    {
        $oldPublicId = $product->cloudinary_public_id;
        $oldImage = $product->image;
        $uploaded = $this->uploadImage($file, 'products/'.$product->id);

        try {
            $this->saveProductImage($product, $uploaded, $this->normalizeCrop($crop, $uploaded));
        } catch (Throwable $exception) {
            $this->tryDelete($uploaded->publicId);
            throw new RuntimeException('Product image could not be replaced.', 0, $exception);
        }

        try {
            if ($oldPublicId) {
                $this->deleteImage($oldPublicId);
            } elseif ($oldImage) {
                Storage::disk('local')->delete('public/products/'.basename($oldImage));
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function deleteProductImage(Product $product): void
    {
        if ($product->cloudinary_public_id) {
            $this->deleteImage($product->cloudinary_public_id);
            return;
        }

        if ($product->image) {
            Storage::disk('local')->delete('public/products/'.basename($product->image));
        }
    }

    public function removeProductImage(Product $product): void
    {
        $this->deleteProductImage($product);
        $product->forceFill([
            'image' => 'default.jpg',
            'cloudinary_public_id' => null,
            'cloudinary_version' => null,
            'cloudinary_format' => null,
            'cloudinary_resource_type' => null,
            'cloudinary_bytes' => null,
            'cloudinary_width' => null,
            'cloudinary_height' => null,
            'image_crop' => null,
        ])->saveOrFail();
    }

    public function getImageUrl(string $publicId, int $width = 1200, ?int $height = null, ?array $crop = null): string
    {
        $this->validateDimensions($width, $height ?? $width);

        $resize = [
            'width' => $width,
            'height' => $height ?? $width,
            'crop' => 'fill',
            'gravity' => 'auto',
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ];

        $transformation = $crop
            ? Transformation::fromParams([
                'x' => $crop['x'],
                'y' => $crop['y'],
                'width' => $crop['width'],
                'height' => $crop['height'],
                'crop' => 'crop',
                'angle' => (int) ($crop['rotation'] ?? 0),
            ])->addTransformation(Transformation::fromParams($resize))
            : Transformation::fromParams($resize);

        return (string) $this->cloudinary->image($this->safePublicId($publicId))->toUrl($transformation);
    }

    public function getThumbnailUrl(string $publicId, int $size = 320): string
    {
        return $this->getImageUrl($publicId, $size, $size);
    }

    public function getProductVariants(string $publicId, ?array $crop = null): array
    {
        return [
            'thumb' => $this->getImageUrl($publicId, 150, 150, $crop),
            'card' => $this->getImageUrl($publicId, 400, 400, $crop),
            'display' => $this->getImageUrl($publicId, 800, 800, $crop),
            'large' => $this->getImageUrl($publicId, 1000, 1000, $crop),
        ];
    }

    private function uploadRemoteImage(UploadedFile $file, string $folder): ImageResult
    {
        $publicId = trim($folder, '/').'/image-'.Str::lower(Str::random(16));

        try {
            $response = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'public_id' => $publicId,
                'resource_type' => 'image',
                'use_filename' => false,
                'unique_filename' => false,
                'overwrite' => false,
            ]);

            return new ImageResult(
                publicId: (string) $response['public_id'],
                secureUrl: (string) ($response['secure_url'] ?? $response['url']),
                format: $response['format'] ?? null,
                width: isset($response['width']) ? (int) $response['width'] : null,
                height: isset($response['height']) ? (int) $response['height'] : null,
                bytes: isset($response['bytes']) ? (int) $response['bytes'] : null,
                version: isset($response['version']) ? (string) $response['version'] : null,
                resourceType: (string) ($response['resource_type'] ?? 'image'),
            );
        } catch (Throwable $exception) {
            $this->tryDelete($publicId);

            throw new RuntimeException('Cloudinary image upload failed.', 0, $exception);
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        if (! $file->isValid() || ! in_array($file->getMimeType(), self::MIME_TYPES, true)) {
            throw new RuntimeException('Unsupported image. Use JPEG, PNG, or WEBP.');
        }

        if ($file->getSize() > self::MAX_KILOBYTES * 1024) {
            throw new RuntimeException('Image exceeds the 5 MB limit.');
        }

        $dimensions = @getimagesize($file->getRealPath());
        if (! is_array($dimensions) || ($dimensions[0] ?? 0) < self::MIN_DIMENSION || ($dimensions[1] ?? 0) < self::MIN_DIMENSION) {
            throw new RuntimeException('Image must be at least 300 by 300 pixels.');
        }

        if (($dimensions[0] * $dimensions[1]) > self::MAX_PIXELS) {
            throw new RuntimeException('Image dimensions are too large to process safely.');
        }
    }

    private function validateFolder(string $folder): void
    {
        if ($folder === '' || ! preg_match('/^[a-z0-9][a-z0-9\/_-]*$/i', $folder)) {
            throw new RuntimeException('Invalid image folder.');
        }
    }

    private function validateDimensions(int $width, int $height): void
    {
        if ($width < 1 || $width > 4000 || $height < 1 || $height > 4000) {
            throw new RuntimeException('Image dimensions are outside the supported range.');
        }
    }

    private function safePublicId(string $publicId): string
    {
        if (! $this->isSafePublicId($publicId)) {
            throw new RuntimeException('Invalid Cloudinary public ID.');
        }

        return $publicId;
    }

    private function isSafePublicId(string $publicId): bool
    {
        return $publicId !== '' && (bool) preg_match('/^[a-z0-9][a-z0-9\/_-]*$/i', $publicId);
    }

    private function tryDelete(string $publicId): void
    {
        try {
            $this->deleteImage($publicId);
        } catch (Throwable) {
        }
    }

    private function saveProductImage(Product $product, ImageResult $image, ?array $crop = null): void
    {
        $product->forceFill([
            'image' => $image->secureUrl,
            'cloudinary_public_id' => $image->publicId,
            'cloudinary_version' => $image->version,
            'cloudinary_format' => $image->format,
            'cloudinary_resource_type' => $image->resourceType,
            'cloudinary_bytes' => $image->bytes,
            'cloudinary_width' => $image->width,
            'cloudinary_height' => $image->height,
            'image_crop' => $crop,
        ])->saveOrFail();
    }

    private function normalizeCrop(?array $crop, ImageResult $image): ?array
    {
        if ($crop === null) {
            return null;
        }

        $sourceWidth = max(1, (int) $image->width);
        $sourceHeight = max(1, (int) $image->height);
        $x = max(0, min($sourceWidth - 1, (int) round($crop['x'] ?? 0)));
        $y = max(0, min($sourceHeight - 1, (int) round($crop['y'] ?? 0)));
        $size = max(1, (int) round($crop['size'] ?? min($sourceWidth - $x, $sourceHeight - $y)));
        $size = min($size, $sourceWidth - $x, $sourceHeight - $y);

        return ['x' => $x, 'y' => $y, 'width' => $size, 'height' => $size];
    }
}
