<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
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
        'image/gif',
    ];

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

    public function getImageUrl(string $publicId, int $width = 1200, ?int $height = null): string
    {
        $this->validateDimensions($width, $height ?? $width);

        return (string) $this->cloudinary->image($this->safePublicId($publicId))->toUrl([
            'width' => $width,
            'height' => $height ?? $width,
            'crop' => 'fill',
            'gravity' => 'auto',
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ]);
    }

    public function getThumbnailUrl(string $publicId, int $size = 320): string
    {
        return $this->getImageUrl($publicId, $size, $size);
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
            throw new RuntimeException('Unsupported image. Use JPEG, PNG, WEBP, or GIF.');
        }

        if ($file->getSize() > self::MAX_KILOBYTES * 1024) {
            throw new RuntimeException('Image exceeds the 5 MB limit.');
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
}
