<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make user-facing content fields translatable (spatie/laravel-translatable).
 * Each column stores JSON like {"en": "...", "ar": "..."}; existing plain values
 * are wrapped as {"en": value}. Columns are widened to TEXT to hold both locales.
 * Slugs, types, seasons, urls and images stay single-value (not translated).
 */
return new class extends Migration
{
    private array $map = [
        'products' => ['name', 'description', 'fabric', 'meta_title', 'meta_description'],
        'categories' => ['name', 'description'],
        'collections' => ['name', 'description'],
        'showcases' => ['title', 'subtitle', 'description'],
    ];

    public function up(): void
    {
        // 1) Wrap existing plain (non-JSON) values into {"en": value}.
        foreach ($this->map as $table => $cols) {
            foreach ($cols as $col) {
                DB::statement("UPDATE `{$table}` SET `{$col}` = JSON_OBJECT('en', `{$col}`) WHERE `{$col}` IS NOT NULL AND JSON_VALID(`{$col}`) = 0");
            }
        }

        // 2) Widen string columns to TEXT so they can hold bilingual JSON.
        Schema::table('products', function (Blueprint $t) {
            $t->text('name')->change();
            $t->text('fabric')->nullable()->change();
            $t->text('meta_title')->nullable()->change();
            $t->text('meta_description')->nullable()->change();
        });
        Schema::table('categories', fn (Blueprint $t) => $t->text('name')->change());
        Schema::table('collections', fn (Blueprint $t) => $t->text('name')->change());
        Schema::table('showcases', function (Blueprint $t) {
            $t->text('title')->change();
            $t->text('subtitle')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Collapse JSON back to the English value, then narrow widened columns.
        foreach ($this->map as $table => $cols) {
            foreach ($cols as $col) {
                DB::statement("UPDATE `{$table}` SET `{$col}` = JSON_UNQUOTE(JSON_EXTRACT(`{$col}`, '$.en')) WHERE `{$col}` IS NOT NULL AND JSON_VALID(`{$col}`) = 1");
            }
        }

        Schema::table('products', function (Blueprint $t) {
            $t->string('name')->change();
            $t->string('fabric')->nullable()->change();
            $t->string('meta_title')->nullable()->change();
            $t->string('meta_description')->nullable()->change();
        });
        Schema::table('categories', fn (Blueprint $t) => $t->string('name')->change());
        Schema::table('collections', fn (Blueprint $t) => $t->string('name')->change());
        Schema::table('showcases', function (Blueprint $t) {
            $t->string('title')->change();
            $t->string('subtitle')->nullable()->change();
        });
    }
};
