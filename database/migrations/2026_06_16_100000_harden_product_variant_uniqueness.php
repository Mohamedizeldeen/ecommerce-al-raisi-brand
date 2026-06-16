<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make variant size/color NOT NULL DEFAULT '' so the (product_id, size, color)
 * unique index actually prevents duplicate "default" variants — MySQL treats
 * each NULL as distinct, which let duplicates slip through and inflate stock sums.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE product_variants SET size = '' WHERE size IS NULL");
        DB::statement("UPDATE product_variants SET color = '' WHERE color IS NULL");

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('size')->nullable(false)->default('')->change();
            $table->string('color')->nullable(false)->default('')->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('size')->nullable()->default(null)->change();
            $table->string('color')->nullable()->default(null)->change();
        });
    }
};
