<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('join_request_votes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('join_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('vote')->default('accept');
            $table->timestamps();

            $table->unique(['join_request_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('join_request_votes');
    }
};
