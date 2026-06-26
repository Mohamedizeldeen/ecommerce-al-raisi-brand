<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inclusive VAT broken out on each order: tax_baisa is the VAT component of the
     * total (which already includes it), and vat_percent records the rate applied at
     * purchase time so historical receipts stay accurate if the rate later changes.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_baisa')->default(0)->after('discount_baisa');
            $table->unsignedTinyInteger('vat_percent')->default(0)->after('tax_baisa');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tax_baisa', 'vat_percent']);
        });
    }
};
