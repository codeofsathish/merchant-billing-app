<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->dateTime('period_start');
            $table->dateTime('period_end');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('status')->default('issued');
            $table->timestamps();
            $table->unique(['subscription_id','period_start']);
            $table->index(['merchant_id','period_end']);
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); }
};
