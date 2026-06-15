<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                       // snapshot of product name
            $table->string('variant_label')->nullable();  // e.g. "M / White Rose Print"
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('price_baisa');    // snapshot of unit price
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_baisa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
