<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow the same bill number for different parties.
     */
    public function up(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->dropUnique('vashi_market_bills_bill_no_unique');
            $table->unique(['bill_no', 'party_name'], 'vashi_bills_bill_no_party_unique');
        });
    }

    /**
     * Restore the global bill number unique index.
     */
    public function down(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->dropUnique('vashi_bills_bill_no_party_unique');
            $table->unique('bill_no', 'vashi_market_bills_bill_no_unique');
        });
    }
};
