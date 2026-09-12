<?php

namespace App\Services;

final readonly class ImageResult
{
    public function __construct(
        public string $publicId,
        public string $secureUrl,
        public ?string $format,
        public ?int $width,
        public ?int $height,
        public ?int $bytes,
        public ?string $version,
        public string $resourceType = 'image',
    ) {}

    public function metadata(): array
    {
        return [
            'public_id' => $this->publicId,
            'secure_url' => $this->secureUrl,
            'format' => $this->format,
            'width' => $this->width,
            'height' => $this->height,
            'bytes' => $this->bytes,
            'version' => $this->version,
            'resource_type' => $this->resourceType,
        ];
    }
}
