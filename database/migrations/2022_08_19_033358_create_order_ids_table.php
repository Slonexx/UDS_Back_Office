<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderIdsTable extends Migration
{

    public function up()
    {
        Schema::create('order_ids', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->integer('orderID');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('order_ids');
    }
}
