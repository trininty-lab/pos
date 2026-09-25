<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('scope', 20)->default('business')->after('guard_name');
            $table->index('scope');
        });

        DB::table('roles')->where('name', 'super-admin')->update(['scope' => 'system']);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex(['scope']);
            $table->dropColumn('scope');
        });
    }
};