<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('cloudinary_public_id')->nullable()->unique()->after('image');
            $table->string('cloudinary_version')->nullable()->after('cloudinary_public_id');
            $table->string('cloudinary_format', 20)->nullable()->after('cloudinary_version');
            $table->string('cloudinary_resource_type', 20)->nullable()->after('cloudinary_format');
            $table->unsignedBigInteger('cloudinary_bytes')->nullable()->after('cloudinary_resource_type');
            $table->unsignedInteger('cloudinary_width')->nullable()->after('cloudinary_bytes');
            $table->unsignedInteger('cloudinary_height')->nullable()->after('cloudinary_width');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['cloudinary_public_id']);
            $table->dropColumn([
                'cloudinary_public_id',
                'cloudinary_version',
                'cloudinary_format',
                'cloudinary_resource_type',
                'cloudinary_bytes',
                'cloudinary_width',
                'cloudinary_height',
            ]);
        });
    }
};
