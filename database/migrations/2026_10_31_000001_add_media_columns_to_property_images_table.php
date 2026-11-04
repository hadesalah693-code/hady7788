<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_images', function (Blueprint $table) {
            $table->string('disk', 30)->default('public')->after('path');
            $table->string('mime_type', 100)->nullable()->after('disk');
            $table->unsignedBigInteger('size')->nullable()->after('mime_type');
        });

        // Guarantee a single cover image per property even under concurrent
        // requests. Existing rows are safe: every property already has at most
        // one image flagged as cover.
        DB::statement(
            'CREATE UNIQUE INDEX property_images_one_cover_per_property
             ON property_images (property_id)
             WHERE is_cover = true'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS property_images_one_cover_per_property');

        Schema::table('property_images', function (Blueprint $table) {
            $table->dropColumn(['disk', 'mime_type', 'size']);
        });
    }
};
