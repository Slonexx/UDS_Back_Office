<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutomationNewUpdateMODEL extends Migration
{

    public function up()
    {
        Schema::table('automation_new_update__m_o_d_e_l_s', function (Blueprint $table) {
            $table->string('documentAutomation')->nullable();
        });
    }


    public function down()
    {
        Schema::table('automation_new_update__m_o_d_e_l_s', function (Blueprint $table) {
            $table->dropColumn('add_automationPaymentDocument');
        });
    }
}
