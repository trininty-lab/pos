<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedBigInteger('maximum_sell_price')->default(0)->after('minimum_sell_price');
        });

        DB::table('products')->update(['maximum_sell_price' => DB::raw('sell_price')]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('maximum_sell_price');
        });
    }
};