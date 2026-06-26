<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft-deletes protect financial records (orders must never be truly destroyed)
     * and let the shop retire catalog entries without breaking order history or the
     * refund/restock paths (a soft-deleted variant row still exists for order_items).
     */
    public function up(): void
    {
        foreach (['orders', 'products', 'product_variants'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
        }
    }

    public function down(): void
    {
        foreach (['orders', 'products', 'product_variants'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
        }
    }
};
