<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('event_key', 191);
            $table->dateTime('occurred_at');
            $table->date('usage_date');
            $table->unsignedInteger('units');
            $table->string('type', 50)->default('api');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Critical for retry-safe high-throughput ingestion.
            $table->unique(['merchant_id','customer_id','event_key'], 'usage_event_idempotency');
            $table->index(['merchant_id','customer_id','occurred_at']);
            $table->index(['merchant_id','usage_date','id']);
        });
    }
    public function down(): void { Schema::dropIfExists('usage_events'); }
};
