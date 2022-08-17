<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookClintLogsTable extends Migration
{

    public function up()
    {
        Schema::create('webhook_clint_logs', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('companyId');
            $table->longText('message');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('webhook_clint_logs');
    }
}
