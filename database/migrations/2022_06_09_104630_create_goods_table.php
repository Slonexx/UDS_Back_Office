<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTable extends Migration
{

    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->string("id_MC");

            $table->string("name");

            $table->float("price");

            $table->boolean("offerPrice_type");

            $table->float("offerPrice");

            $table->string("article")->nullable();

            $table->string("description");

            $table->timestamps();

            $table->softDeletes();
        });
    }


    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
