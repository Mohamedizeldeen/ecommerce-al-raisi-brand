<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional "compare-at" (was) price. When set above base_price_baisa, the
     * storefront treats base_price_baisa as the sale price and shows the compare-at
     * struck through with a Sale badge. Stored as integer baisa like every price.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('compare_at_price_baisa')->nullable()->after('base_price_baisa');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('compare_at_price_baisa');
        });
    }
};
