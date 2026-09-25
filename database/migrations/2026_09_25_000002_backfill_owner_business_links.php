<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('businesses') || ! Schema::hasTable('users')) {
            return;
        }

        DB::table('users as u')
            ->join('businesses as b', 'b.owner_id', '=', 'u.id')
            ->where(function ($query) {
                $query->whereNull('u.business_id')
                    ->orWhereColumn('u.business_id', '!=', 'b.id');
            })
            ->update([
                'u.business_id' => DB::raw('b.id'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this is a data repair migration and should not erase user business assignments.
    }
};
