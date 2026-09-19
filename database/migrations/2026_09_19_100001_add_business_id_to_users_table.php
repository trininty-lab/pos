<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('business_id')->nullable()->after('id')->constrained('businesses')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->timestamp('suspended_at')->nullable()->after('last_login_at');
            $table->boolean('force_password_reset')->default(false)->after('suspended_at');
            $table->index(['business_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['business_id']);
            $table->dropIndex(['business_id', 'is_active']);
            $table->dropColumn([
                'business_id',
                'is_active',
                'last_login_at',
                'suspended_at',
                'force_password_reset',
            ]);
        });
    }
};
