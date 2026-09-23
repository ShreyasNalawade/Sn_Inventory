<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create per-bill allocation rows for a group payment.
     * Existing bill rows are never deleted by this migration.
     */
    public function up(): void
    {
        Schema::create('vashi_market_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vashi_market_payment_id')
                ->constrained('vashi_market_payments')
                ->cascadeOnDelete();
            $table->foreignId('vashi_market_bill_id')
                ->constrained('vashi_market_bills')
                ->restrictOnDelete();
            $table->decimal('bill_amount', 12, 2);
            $table->decimal('allocated_amount', 12, 2);
            $table->decimal('interest_difference', 12, 2)->default(0);
            $table->decimal('interest_percentage', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['vashi_market_payment_id', 'vashi_market_bill_id'],
                'vashi_payment_bill_unique'
            );
            $table->index('vashi_market_bill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vashi_market_payment_allocations');
    }
};
