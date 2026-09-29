<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_period_id')->constrained()->restrictOnDelete();
            $table->string('description');
            $table->decimal('base_amount', 12, 2);
            $table->decimal('included_units', 18, 4);
            $table->decimal('used_units', 18, 4);
            $table->decimal('overage_units', 18, 4);
            $table->decimal('overage_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoice_lines'); }
};
