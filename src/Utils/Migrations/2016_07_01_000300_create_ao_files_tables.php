<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAoFilesTables extends Migration
{

    public function up()
    {
        Schema::create('ao_files_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('folder');
            $table->string('name');
            $table->string('extension', 10);
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('ao_files_files');
    }

}
