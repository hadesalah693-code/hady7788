<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('name_ar', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['parent_id', 'is_active', 'sort_order']);
        });

        Schema::create('technician_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name', 150);
            $table->string('professional_title', 150)->nullable();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->string('verification_status', 20)->default('pending');
            $table->string('availability_status', 20)->default('available');
            $table->string('approval_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['verification_status', 'availability_status']);
        });

        Schema::create('technician_specializations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('technician_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->decimal('starting_price', 10, 2)->nullable();
            $table->boolean('accepts_quotes')->default(true);
            $table->timestamps();
            $table->unique(['technician_profile_id', 'service_category_id'], 'technician_specialization_unique');
        });

        Schema::create('technician_service_areas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('technician_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['technician_profile_id', 'governorate_id', 'city_id'], 'technician_area_unique');
        });

        Schema::create('service_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('technician_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('governorate_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 180);
            $table->text('description');
            $table->text('private_address');
            $table->string('preferred_time', 80)->nullable();
            $table->string('status', 24)->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['service_category_id', 'governorate_id', 'status'], 'service_request_search_idx');
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('service_request_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 40)->default('local');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        Schema::create('service_quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->text('details')->nullable();
            $table->unsignedSmallInteger('estimated_duration_hours')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['service_request_id', 'technician_profile_id'], 'service_quote_technician_unique');
            $table->index(['technician_profile_id', 'status']);
        });

        Schema::create('service_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('technician_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->index(['technician_profile_id', 'is_visible', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_reviews');
        Schema::dropIfExists('service_quotes');
        Schema::dropIfExists('service_request_images');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('technician_service_areas');
        Schema::dropIfExists('technician_specializations');
        Schema::dropIfExists('technician_profiles');
        Schema::dropIfExists('service_categories');
    }
};
