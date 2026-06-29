<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog categories — the SDM "Blog Category" routing layer that groups articles by
 * topic (e.g. Styling Guides, Cultural Heritage). `name`/`description` hold
 * bilingual JSON (spatie/laravel-translatable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->text('name');                 // translatable {"en":..,"ar":..}
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // translatable
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};
