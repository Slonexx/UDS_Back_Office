<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookOrderLogsTable extends Migration
{

    public function up()
    {
        Schema::create('webhook_order_logs', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->longText('message');
            $table->string('companyId');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_order_logs');
    }
}
