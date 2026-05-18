<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title_en');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->text('description_en');
            $table->text('description_ar')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->enum('category', ['concerts', 'sports', 'business', 'cultural', 'food', 'arts', 'family', 'nightlife', 'other']);
            $table->string('cover_image', 500)->nullable();
            $table->json('gallery')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('has_tickets')->default(false);
            $table->boolean('is_free')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('attending_count')->default(0);
            $table->integer('save_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organizer_id', 'status']);
            $table->index(['venue_id', 'start_date']);
            $table->index(['category', 'status', 'start_date']);
            $table->index(['status', 'start_date']);
            $table->index('slug');
            $table->fullText(['title_en', 'title_ar', 'description_en', 'description_ar']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
