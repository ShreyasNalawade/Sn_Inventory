<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speed up the daily report groupings.
     */
    public function up(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->index('is_paid', 'vashi_bills_is_paid_index');
            $table->index('dalal', 'vashi_bills_dalal_index');
            $table->index('received_date', 'vashi_bills_received_date_index');
        });
    }

    /**
     * Remove the daily report indexes.
     */
    public function down(): void
    {
        Schema::table('vashi_market_bills', function (Blueprint $table) {
            $table->dropIndex('vashi_bills_is_paid_index');
            $table->dropIndex('vashi_bills_dalal_index');
            $table->dropIndex('vashi_bills_received_date_index');
        });
    }
};
