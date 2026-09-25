<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StoreProfileSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setMany([
            'store_name' => [
                'value' => env('POS_STORE_NAME', 'FAY COLLECTION'),
                'description' => 'Store name',
            ],
            'store_email' => [
                'value' => env('POS_OWNER_EMAIL', 'fayfaithbitokeld@gmail.com'),
                'description' => 'Store email',
            ],
        ]);
    }
}