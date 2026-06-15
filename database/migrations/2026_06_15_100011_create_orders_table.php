<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');

            $table->unsignedBigInteger('subtotal_baisa')->default(0);
            $table->unsignedBigInteger('shipping_baisa')->default(0);
            $table->unsignedBigInteger('discount_baisa')->default(0);
            $table->unsignedBigInteger('total_baisa')->default(0);
            $table->string('currency', 3)->default('OMR');
            $table->string('coupon_code')->nullable();

            // Payment gateway correlation
            $table->string('thawani_session_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Contact + shipping snapshot
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('shipping_address_line1')->nullable();
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_region')->nullable();
            $table->string('shipping_country')->default('Oman');

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payment_status', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
