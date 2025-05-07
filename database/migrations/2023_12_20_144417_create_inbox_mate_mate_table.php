<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbox_mate_mate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique('uid');
            $table->string('user1')->nullable()->index('created_by');
            $table->string('user2')->nullable()->index('user2');
            $table->timestamps();
            $table->timestamp('user1_checked')->nullable();
            $table->timestamp('user2_checked')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inbox_mate_mate');
    }
};
