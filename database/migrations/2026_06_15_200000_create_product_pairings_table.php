<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Style it with" — suggested matching pieces for a product (belt, bag,
     * scarf, jewellery…). Directional: product_id is the outfit, paired the suggestion.
     */
    public function up(): void
    {
        Schema::create('product_pairings', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paired_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['product_id', 'paired_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pairings');
    }
};
