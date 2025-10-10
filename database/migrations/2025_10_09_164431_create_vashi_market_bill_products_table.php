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
        Schema::create('vashi_market_bill_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vashi_market_bill_id')->constrained('vashi_market_bills')->onDelete('cascade');
            $table->string('product_name');
            $table->string('brand_name')->nullable();
            $table->integer('num_bags');
            $table->decimal('bag_size', 8, 2);
            $table->decimal('total_kg', 8, 2);
            $table->decimal('rate', 8, 2);
            $table->decimal('product_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vashi_market_bill_products');
    }
};