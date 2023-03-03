<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationNewUpdateMODELSTable extends Migration
{

    public function up()
    {
        Schema::create('automation_new_update__m_o_d_e_l_s', function (Blueprint $table) {
            $table->id();
            $table->string('accountId')->nullable();
            $table->foreign('accountId')->references('accountId')->on('setting_mains')->cascadeOnDelete();

            $table->string('activateAutomation')->nullable();
            $table->string('statusAutomation')->nullable();
            $table->string('projectAutomation')->nullable();
            $table->string('saleschannelAutomation')->nullable();

            $table->string('automationDocument')->nullable();
            $table->string('add_automationStore')->nullable();
            $table->string('add_automationPaymentDocument')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('automation_new_update__m_o_d_e_l_s');
    }
}
