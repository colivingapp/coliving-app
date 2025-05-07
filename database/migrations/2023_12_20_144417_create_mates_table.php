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
        Schema::create('mates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('uid')->index('uid');
            $table->integer('private')->default(0);
            $table->string('username')->default('')->unique();
            $table->string('name')->default('');
            $table->string('profile_pic')->default('avatar');
            $table->string('avatar')->default('');
            $table->string('photo')->default('');
            $table->string('hobbies_interests')->default('');
            $table->string('twitter')->default('');
            $table->string('facebook')->default('');
            $table->string('instagram')->default('');
            $table->string('youtube')->default('');
            $table->string('tiktok')->default('');
            $table->string('pinterest')->default('');
            $table->string('linkedin')->default('');
            $table->string('github')->default('');
            $table->string('substack')->default('');
            $table->string('medium')->default('');
            $table->integer('orgSpont')->default(5);
            $table->integer('quietLoud')->default(5);
            $table->integer('earlyNight')->default(5);
            $table->integer('workFun')->default(5);
            $table->integer('expTrad')->default(5);
            $table->integer('multiLocal')->default(5);
            $table->timestamps();

            $table->unique(['uid'], 'uid_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mates');
    }
};
