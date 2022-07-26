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

            $table->integer("stock");

            $table->boolean("offerPrice_type");

            $table->boolean("offerSkipLoyalty");

            $table->float("offerPrice");

            $table->string("article");

            $table->string("description");

            $table->string("photos");

            $table->string("measurement");

            $table->string("type_CATEGORY");

            $table->timestamps();

            $table->softDeletes();
        });
    }


    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
