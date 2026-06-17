<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for common storefront queries: price sorting, and product-leading lookups
 * on the pivot tables (their composite PKs lead with category_id/collection_id, so a
 * "products in X" → "X of this product" reverse lookup can't use them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->index('base_price_baisa'));
        Schema::table('category_product', fn (Blueprint $t) => $t->index('product_id', 'category_product_product_id_index'));
        Schema::table('collection_product', fn (Blueprint $t) => $t->index('product_id', 'collection_product_product_id_index'));
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropIndex(['base_price_baisa']));
        Schema::table('category_product', fn (Blueprint $t) => $t->dropIndex('category_product_product_id_index'));
        Schema::table('collection_product', fn (Blueprint $t) => $t->dropIndex('collection_product_product_id_index'));
    }
};
