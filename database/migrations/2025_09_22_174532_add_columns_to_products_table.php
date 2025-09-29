<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('size_750ml')->nullable();
            $table->string('size_1L')->nullable();
            $table->string('size_3L')->nullable();
            $table->string('size_5L')->nullable();
            $table->string('size_15L_tin')->nullable();
            $table->string('size_15L_jar')->nullable();
            $table->string('type_product')->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'size_750ml',
                'size_1L',
                'size_3L',
                'size_5L',
                'size_15L_tin',
                'size_15L_jar',
                'type_product',
            ]);
        });
    }

};
