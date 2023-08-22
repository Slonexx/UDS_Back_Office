<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewProductModelsTable extends Migration
{

    public function up()
    {
        Schema::create('new_product_models', function (Blueprint $table) {
            $table->id();

            $table->string('accountId');
            $table->string('ProductFolder');
            $table->string('unloading')->nullable();
            $table->string('salesPrices')->nullable();
            $table->string('promotionalPrice')->nullable();
            $table->string('Store')->nullable();
            $table->string('StoreRecord')->nullable();
            $table->string('productHidden')->nullable();
            $table->string('countRound')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('new_product_models');
    }
}
