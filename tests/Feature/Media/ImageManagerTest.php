<?php

namespace Tests\Feature\Media;

use App\Services\ImageManager;
use Tests\TestCase;

class ImageManagerTest extends TestCase
{
    public function test_it_exposes_central_image_type_configuration(): void
    {
        $this->assertSame('PRODUCT_IMAGE', ImageManager::typeKey('PRODUCT_IMAGE'));
        $this->assertSame('products', config('media.image_types.PRODUCT_IMAGE.storage_path'));
        $this->assertSame(['image/jpeg', 'image/png', 'image/webp'], config('media.image_types.PRODUCT_IMAGE.allowed_formats'));
        $this->assertSame('circle', config('media.image_types.USER_AVATAR.crop_mode'));
    }

    public function test_it_returns_algorithmic_default_image_policy_for_new_modules(): void
    {
        $config = ImageManager::forType('STORE_LOGO');

        $this->assertSame('stores/logos', $config['storage_path']);
        $this->assertSame('landscape', $config['aspect_ratio']);
        $this->assertFalse($config['multiple']);
    }
}
