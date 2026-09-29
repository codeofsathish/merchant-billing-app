<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('active');
            $table->dateTime('current_period_start');
            $table->dateTime('current_period_end');
            $table->timestamps();
            $table->index(['merchant_id','status','current_period_end']);
            $table->index(['customer_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
