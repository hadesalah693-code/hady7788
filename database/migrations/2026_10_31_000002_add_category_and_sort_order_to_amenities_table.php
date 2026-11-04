<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->string('category', 30)->nullable()->after('slug');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('category');

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'sort_order']);
        });
    }
};
