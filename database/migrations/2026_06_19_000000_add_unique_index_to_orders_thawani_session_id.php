<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Thawani webhook and the checkout-success page both resolve the order by
     * thawani_session_id, so it needs an index (the column was previously unindexed,
     * forcing a full table scan on every webhook delivery). A Thawani session maps to
     * exactly one order, so make it unique to enforce that invariant at the DB level
     * (nullable unique still allows many NULLs for orders without a session yet).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unique('thawani_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['thawani_session_id']);
        });
    }
};
