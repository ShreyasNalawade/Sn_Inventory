<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vashi_market_bills', function (Blueprint $table) {
            $table->id();
            $table->date('bill_date');
            $table->date('received_date');
            $table->string('bill_no')->unique();
            $table->string('party_name');
            $table->string('dalal');
            $table->string('transport_name');
            $table->decimal('total_bill_amount', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->date('paid_date')->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vashi_market_bills');
    }
};