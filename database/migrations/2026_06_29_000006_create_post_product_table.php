<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links editorial posts to commercial products — the SDM "Shop this article"
 * integration that lets blog content drive product discovery.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_product', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['post_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_product');
    }
};
