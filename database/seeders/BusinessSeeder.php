<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', env('POS_OWNER_EMAIL', 'fayfaithbitokeld@gmail.com'))->first();
        $cashier = User::where('email', 'cashier@gmail.com')->first();

        if (! $owner) {
            return;
        }

        $business = Business::updateOrCreate(
            ['registration_number' => 'POS-DEMO-001'],
            [
                'name' => env('POS_STORE_NAME', 'FAY COLLECTION'),
                'email' => $owner->email,
                'country' => 'KE',
                'currency' => 'KES',
                'status' => 'active',
                'owner_id' => $owner->id,
                'last_activity_at' => now(),
            ]
        );

        $owner->forceFill(['business_id' => $business->id])->save();
        $cashier?->forceFill(['business_id' => $business->id])->save();
    }
}
