<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price', 10, 2);
            $table->char('currency', 3)->default('EGP');
            $table->integer('quantity_total');
            $table->integer('quantity_sold')->default(0);
            $table->timestamp('sales_start')->nullable();
            $table->timestamp('sales_end')->nullable();
            $table->integer('max_per_order')->default(10);
            $table->integer('min_per_order')->default(1);
            $table->enum('status', ['available', 'sold_out', 'inactive'])->default('available');
            $table->timestamps();
            $table->index(['event_id', 'status']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);
            $table->integer('quantity');
            $table->decimal('subtotal_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('fees_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->char('currency', 3)->default('EGP');
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->enum('payment_method', ['paymob', 'fawry', 'cash'])->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index('order_number');
            $table->index(['status', 'created_at']);
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_paid', 10, 2);
            $table->char('currency', 3)->default('EGP');
            $table->string('holder_name');
            $table->string('holder_email');
            $table->string('holder_phone', 20)->nullable();
            $table->string('qr_code_url', 500)->nullable();
            $table->enum('status', ['valid', 'used', 'cancelled', 'refunded'])->default('valid');
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('staff_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('order_id');
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('ticket_code');
            $table->index(['checked_in', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('ticket_types');
    }
};
