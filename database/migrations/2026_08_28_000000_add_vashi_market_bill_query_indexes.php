<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes used by the bill list filters.
     */
    public function up(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->index('bill_date', 'vashi_bills_bill_date_index');
            $table->index('party_name', 'vashi_bills_party_name_index');
        });

        Schema::table('vashi_market_bill_products', function (Blueprint $table) {
            $table->index('product_name', 'vashi_bill_products_product_name_index');
        });
    }

    /**
     * Remove the bill list filter indexes.
     */
    public function down(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->dropIndex('vashi_bills_bill_date_index');
            $table->dropIndex('vashi_bills_party_name_index');
        });

        Schema::table('vashi_market_bill_products', function (Blueprint $table) {
            $table->dropIndex('vashi_bill_products_product_name_index');
        });
    }
};
