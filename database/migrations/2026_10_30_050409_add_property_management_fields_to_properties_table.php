<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('currency', 8)->default('EGP')->after('price');
            $table->string('finishing', 30)->nullable()->after('floor');
            $table->unsignedSmallInteger('total_floors')->nullable()->after('finishing');
            $table->unsignedSmallInteger('parking_spaces')->nullable()->after('total_floors');
            $table->unsignedSmallInteger('year_built')->nullable()->after('parking_spaces');
            $table->date('available_from')->nullable()->after('year_built');
            $table->string('contact_name', 120)->nullable()->after('address');
            $table->string('contact_phone', 20)->nullable()->after('contact_name');
            $table->string('contact_whatsapp', 20)->nullable()->after('contact_phone');
            $table->foreignId('published_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();

            $table->index('finishing');
            $table->index('currency');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropIndex(['finishing']);
            $table->dropIndex(['currency']);
            $table->dropColumn([
                'currency',
                'finishing',
                'total_floors',
                'parking_spaces',
                'year_built',
                'available_from',
                'contact_name',
                'contact_phone',
                'contact_whatsapp',
                'published_by',
            ]);
        });
    }
};
