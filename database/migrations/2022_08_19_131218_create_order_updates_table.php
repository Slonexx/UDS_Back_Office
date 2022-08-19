<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderUpdatesTable extends Migration
{
    public function up()
    {
        Schema::create('order_updates', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->longText('message');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_updates');
    }
}
