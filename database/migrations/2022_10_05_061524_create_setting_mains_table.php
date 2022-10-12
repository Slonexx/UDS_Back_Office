<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingMainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_mains', function (Blueprint $table) {
            $table->string('accountId')->unique()->primary();
            $table->string('TokenMoySklad');
            $table->string('companyId');
            $table->string('TokenUDS');
            $table->string('ProductFolder');
            $table->string('UpdateProduct');
            $table->string('Store');
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
        Schema::dropIfExists('setting_mains');
    }
}
