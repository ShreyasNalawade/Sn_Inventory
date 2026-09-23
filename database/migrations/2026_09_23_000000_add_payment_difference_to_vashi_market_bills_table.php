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
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->decimal('payment_difference', 10, 2)
                ->nullable()
                ->after('paid_amount')
                ->comment('Paid amount minus total bill amount. Positive = interest, negative = offer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->dropColumn('payment_difference');
        });
    }
};
