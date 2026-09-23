<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the group-payment header table.
     * This only adds a new table and does not modify or delete existing bill data.
     */
    public function up(): void
    {
        Schema::create('vashi_market_payments', function (Blueprint $table) {
            $table->id();
            $table->string('party_name');
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->date('paid_date')->nullable();
            $table->decimal('total_bills_amount', 12, 2);
            $table->decimal('total_paid_amount', 12, 2);
            $table->decimal('total_difference', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('party_name');
            $table->index('paid_date');
            $table->index('receipt_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vashi_market_payments');
    }
};
