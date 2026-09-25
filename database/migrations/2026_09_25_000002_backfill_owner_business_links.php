<?php

use Illuminate\Database\Migrations\Migration;
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

        foreach (DB::table('businesses')->select('id', 'owner_id')->get() as $business) {
            DB::table('users')
                ->where('id', $business->owner_id)
                ->where(function ($query) use ($business) {
                    $query->whereNull('business_id')
                        ->orWhere('business_id', '!=', $business->id);
                })
                ->update(['business_id' => $business->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this is a data repair migration and should not erase user business assignments.
    }
};
