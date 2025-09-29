<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT Auto Increment Primary Key
            $table->string('name', 100); // VARCHAR(100)
            $table->decimal('purchase_price', 10, 2); // DECIMAL(10,2)
            $table->timestamps(); // created_at & updated_at (TIMESTAMP)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
