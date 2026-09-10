<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'store_name')
            ->where('value', 'Toko Anda')
            ->update([
                'value' => 'Fay Collection',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'store_name')
            ->where('value', 'Fay Collection')
            ->update([
                'value' => 'Toko Anda',
                'updated_at' => now(),
            ]);
    }
};