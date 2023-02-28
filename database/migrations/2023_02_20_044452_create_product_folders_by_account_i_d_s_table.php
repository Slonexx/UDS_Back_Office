<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductFoldersByAccountIDSTable extends Migration
{

    public function up()
    {
        Schema::create('product_folders_by_account_i_d_s', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->foreign('accountId')->references('accountId')->on('setting_mains')->cascadeOnDelete();
            $table->string('FolderName')->nullable();
            $table->string('FolderID')->nullable();
            $table->string('FolderURLs')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_folders_by_account_i_d_s');
    }
}
