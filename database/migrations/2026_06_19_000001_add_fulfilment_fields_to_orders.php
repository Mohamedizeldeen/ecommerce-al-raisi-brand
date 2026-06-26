<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fulfilment + comms fields: a shipment tracking reference (so staff can record
     * the courier and customers can see it), the shipped timestamp, and the locale the
     * customer checked out in (so transactional emails render in their language).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('carrier')->nullable()->after('thawani_session_id');
            $table->string('tracking_number')->nullable()->after('carrier');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->string('locale', 8)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['carrier', 'tracking_number', 'shipped_at', 'locale']);
        });
    }
};
