<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Descriptive garment type (Search Demand Map req. F): the evergreen
            // vocabulary that drives category landing pages and search filtering,
            // separate from the editorial look name. Nullable so accessories that
            // don't map to a garment type stay untyped. Indexed for fast filtering.
            $table->string('product_type')->nullable()->after('fabric')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type']);
            $table->dropColumn('product_type');
        });
    }
};
