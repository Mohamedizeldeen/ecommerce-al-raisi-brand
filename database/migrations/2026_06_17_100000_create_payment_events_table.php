<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit log of inbound payment events (Thawani webhooks) for reconciliation and
 * explicit duplicate detection — the webhook previously left no trace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('thawani');
            $table->string('thawani_session_id')->nullable()->index();
            $table->string('reference')->nullable();
            $table->string('outcome')->nullable(); // matched | unmatched | paid | not_paid | error
            $table->unsignedBigInteger('amount_baisa')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
