<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CloudinaryImageService
{
    public function upload(UploadedFile $file, string $folder): array
    {
        if (! $this->configured()) {
            $filename = $file->hashName();
            Storage::disk('local')->putFileAs('public/'.$folder, $file, $filename);

            return ['url' => $filename, 'public_id' => null];
        }

        $result = $this->client()->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'image',
        ]);

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    public function delete(?string $publicId): void
    {
        if ($publicId && $this->configured()) {
            $this->client()->uploadApi()->destroy($publicId, [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);
        }
    }

    public function deleteUrl(?string $url): void
    {
        if (! $url || ! str_starts_with($url, 'http')) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || ! preg_match('#/image/upload/(?:v\d+/)?(.+)$#', $path, $matches)) {
            return;
        }

        $this->delete(pathinfo(urldecode($matches[1]), PATHINFO_DIRNAME).'/'.pathinfo($matches[1], PATHINFO_FILENAME));
    }

    private function configured(): bool
    {
        return (bool) (config('services.cloudinary.cloud_url')
            ?: (config('services.cloudinary.cloud_name')
                && config('services.cloudinary.api_key')
                && config('services.cloudinary.api_secret')));
    }

    private function client(): Cloudinary
    {
        $config = config('services.cloudinary');

        return new Cloudinary($config['cloud_url'] ?: [
            'cloud' => [
                'cloud_name' => $config['cloud_name'],
                'api_key' => $config['api_key'],
                'api_secret' => $config['api_secret'],
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }
}