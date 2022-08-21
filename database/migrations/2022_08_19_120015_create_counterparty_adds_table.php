<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounterpartyAddsTable extends Migration
{

    public function up()
    {
        Schema::create('counterparty_adds', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('tokenMC');
            $table->string('companyId');
            $table->string('tokenUDS');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('counterparty_adds');
    }
}
