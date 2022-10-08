<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendOperationsModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_operations_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->foreign('accountId')->references('accountId')->on('setting_mains')->cascadeOnDelete();
            $table->string('operations')->nullable();
            $table->string('EnableOffs')->nullable();
            $table->string('operationsDocument')->nullable();
            $table->string('operationsPaymentDocument')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_operations_models');
    }
}
