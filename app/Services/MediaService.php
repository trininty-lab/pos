<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MediaService
{
    public function upload(UploadedFile $file, string $imageType, ?Model $entity = null, array $context = []): Media
    {
        $typeConfig = ImageManager::forType($imageType);
        $this->validateFile($file, $typeConfig);

        $disk = $context['disk'] ?? config('media.default_disk', 'public');
        $pathPrefix = $context['path'] ?? $typeConfig['storage_path'];
        $filename = $this->generateFilename($file);
        $relativePath = trim($pathPrefix, '/').'/'.$filename;

        Storage::disk($disk)->putFileAs(
            dirname($relativePath),
            $file,
            basename($relativePath),
        );

        $mediaPayload = [
            'disk' => $disk,
            'path' => $relativePath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)),
            'size' => $file->getSize(),
            'width' => $this->widthFromFile($file),
            'height' => $this->heightFromFile($file),
            'image_type' => ImageManager::typeKey($imageType),
            'entity_type' => $entity?->getMorphClass(),
            'entity_id' => $entity?->getKey(),
            'business_id' => $context['business_id'] ?? $entity?->business_id ?? null,
            'store_id' => $context['store_id'] ?? $entity?->store_id ?? null,
            'created_by' => $context['created_by'] ?? auth()->id(),
            'metadata' => [
                'config' => $typeConfig,
                'variants' => $this->buildVariants($relativePath, $disk, $typeConfig),
            ],
        ];

        if ($entity && method_exists($entity, 'media')) {
            return $entity->media()->create($mediaPayload);
        }

        return Media::query()->create($mediaPayload);
    }

    public function replace(UploadedFile $file, Media $media, string $imageType, array $context = []): Media
    {
        $this->delete($media);

        return $this->upload($file, $imageType, $media->entity, array_merge($context, [
            'disk' => $media->disk,
            'path' => $media->path,
            'business_id' => $media->business_id,
            'store_id' => $media->store_id,
            'created_by' => $media->created_by,
        ]));
    }

    public function delete(Media $media): void
    {
        if ($media->path) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();
    }

    public function validateFile(UploadedFile $file, array $config): void
    {
        $allowed = $config['allowed_formats'] ?? ['image/jpeg', 'image/png', 'image/webp'];
        $mime = $file->getMimeType();

        if (! is_string($mime) || ! in_array($mime, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported image format for this image type.');
        }

        if ($file->getSize() > ($config['max_file_size'] ?? 5 * 1024 * 1024)) {
            throw new InvalidArgumentException('The uploaded image is larger than the configured size limit.');
        }

        $info = @getimagesize($file->getRealPath());
        if ($info === false) {
            throw new InvalidArgumentException('The uploaded file is not a valid image.');
        }

        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        $actualMime = $info['mime'] ?? $mime;

        if ($actualMime !== $mime) {
            throw new InvalidArgumentException('Image mime type does not match the uploaded file content.');
        }

        $minWidth = (int) ($config['min_dimensions']['width'] ?? 0);
        $minHeight = (int) ($config['min_dimensions']['height'] ?? 0);
        $maxWidth = (int) ($config['max_dimensions']['width'] ?? 4000);
        $maxHeight = (int) ($config['max_dimensions']['height'] ?? 4000);

        if ($width < $minWidth || $height < $minHeight) {
            throw new InvalidArgumentException('The uploaded image is below the minimum required dimensions.');
        }

        if ($width > $maxWidth || $height > $maxHeight) {
            throw new InvalidArgumentException('The uploaded image exceeds the maximum supported dimensions.');
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        return Str::uuid()->toString().'.'.$extension;
    }

    protected function widthFromFile(UploadedFile $file): ?int
    {
        $info = @getimagesize($file->getRealPath());

        return $info && isset($info[0]) ? (int) $info[0] : null;
    }

    protected function heightFromFile(UploadedFile $file): ?int
    {
        $info = @getimagesize($file->getRealPath());

        return $info && isset($info[1]) ? (int) $info[1] : null;
    }

    protected function buildVariants(string $path, string $disk, array $config): array
    {
        $sizes = $config['thumbnail_sizes'] ?? [120];
        $previewSizes = $config['preview_sizes'] ?? $sizes;
        $variants = [];

        foreach ($sizes as $size) {
            $variants['thumb_'.$size] = $this->variantUrl($path, $disk, $size);
        }

        foreach ($previewSizes as $size) {
            $variants['preview_'.$size] = $this->variantUrl($path, $disk, $size);
        }

        $variants['original'] = $this->variantUrl($path, $disk, null);

        return $variants;
    }

    protected function variantUrl(string $path, string $disk, ?int $size): string
    {
        if ($disk === 's3' || $disk === 'cloudinary') {
            return Storage::disk($disk)->url($path);
        }

        if ($size === null) {
            return Storage::disk($disk)->url($path);
        }

        return Storage::disk($disk)->url($path).'?size='.$size;
    }
}
