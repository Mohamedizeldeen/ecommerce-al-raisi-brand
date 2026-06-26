<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock is now reserved (decremented) at order creation. This marks the single
     * point in time the reservation was returned to the shelf (customer cancel, gateway
     * failure, 24h expiry, admin cancel, or refund) so a release can never run twice.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('stock_released_at')->nullable()->after('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_released_at');
        });
    }
};
