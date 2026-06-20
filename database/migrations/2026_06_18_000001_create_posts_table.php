<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('blog')->index(); // blog | press
            $table->text('title');                  // translatable JSON
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();    // translatable JSON
            $table->longText('body')->nullable();   // translatable JSON (HTML)
            $table->string('cover_image')->nullable();
            $table->text('meta_title')->nullable();        // translatable JSON
            $table->text('meta_description')->nullable();  // translatable JSON
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
