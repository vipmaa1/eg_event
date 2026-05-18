<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique();
            $table->text('bio_en')->nullable();
            $table->text('bio_ar')->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->json('social_media')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('verification_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['slug', 'verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
