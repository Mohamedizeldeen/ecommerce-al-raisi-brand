<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tags — the cross-cutting layer that powers the SDM occasion pages (Wedding Guest,
 * Eid, Resort) and demotes seasons/campaigns from the SEO backbone to lightweight
 * tags. `name`/`description` hold bilingual JSON (spatie/laravel-translatable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->text('name');                 // translatable {"en":..,"ar":..}
            $table->string('slug')->unique();
            $table->string('group')->index();     // App\Enums\TagGroup value
            $table->text('description')->nullable(); // translatable
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
