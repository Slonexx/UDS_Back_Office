<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewAgentModelsTable extends Migration
{

    public function up()
    {
        Schema::create('new_agent_models', function (Blueprint $table) {
            $table->id();

            $table->string('accountId');
            $table->string('unloading')->nullable();
            $table->string('examination')->nullable();
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->string('birthDate')->nullable();

            $table->longText('url')->nullable();
            $table->integer('offset')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('new_agent_models');
    }
}
