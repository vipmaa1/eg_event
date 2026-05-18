<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('address_en')->nullable();
            $table->string('address_ar')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('capacity')->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->json('gallery')->nullable();
            $table->json('amenities')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('verification_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['city', 'verified']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
