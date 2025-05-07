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
        Schema::create('spaces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('uid');
            $table->string('place_id')->default('');
            $table->integer('private')->default(0);
            $table->integer('claimed')->default(0);
            $table->string('name')->default('');
            $table->string('username')->nullable()->unique();
            $table->string('name_slug')->default('');
            $table->string('website')->default('');
            $table->string('email')->default('');
            $table->string('phone')->default('');
            $table->string('country')->default('');
            $table->string('country_slug')->default('');
            $table->string('country_code')->default('');
            $table->string('city')->default('');
            $table->string('city_slug')->default('');
            $table->string('latitude')->default('');
            $table->string('longitude')->default('');
            $table->string('amenities')->default('');
            $table->boolean('photo')->default(false);
            $table->timestamps();
            $table->string('address')->default('');
            $table->string('formatted_address')->default('');
            $table->string('google_url')->default('');
            $table->string('instagram')->default('');
            $table->string('twitter')->default('');
            $table->string('github')->default('');
            $table->string('telegram')->default('');
            $table->string('facebook')->default('');
            $table->string('youtube')->default('');
            $table->string('tiktok')->default('');
            $table->string('pinterest')->default('');
            $table->string('linkedin')->default('');
            $table->string('linktree')->default('');
            $table->string('substack')->default('');
            $table->string('medium')->default('');
            $table->string('booking_com')->default('');
            $table->string('airbnb_com')->default('');
            $table->string('coliving_com')->default('');
            $table->smallInteger('orgSpont')->default(5);
            $table->smallInteger('quietLoud')->default(5);
            $table->smallInteger('earlyNight')->default(5);
            $table->smallInteger('workFun')->default(5);
            $table->smallInteger('expTrad')->default(5);
            $table->smallInteger('multiLocal')->default(5);
            $table->smallInteger('cf_07')->default(5);
            $table->smallInteger('cf_08')->default(5);
            $table->smallInteger('cf_09')->default(5);
            $table->smallInteger('cf_10')->default(5);
            $table->text('description');
            $table->text('info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spaces');
    }
};
