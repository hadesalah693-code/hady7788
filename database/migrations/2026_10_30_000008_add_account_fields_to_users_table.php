<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type', 20)->default('user')->after('email');
            $table->string('status', 20)->default('active')->after('account_type');
            $table->string('phone', 20)->nullable()->unique()->after('password');
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->text('bio')->nullable()->after('whatsapp');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->foreignId('governorate_id')->nullable()->after('avatar_path')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('governorate_id')->constrained()->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('city_id');

            $table->index(['account_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_type', 'status']);
            $table->dropUnique(['phone']);
            $table->dropConstrainedForeignId('governorate_id');
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn([
                'account_type',
                'status',
                'phone',
                'whatsapp',
                'bio',
                'avatar_path',
                'last_login_at',
            ]);
        });
    }
};
