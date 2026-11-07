<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
    }
};
