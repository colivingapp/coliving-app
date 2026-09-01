<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hub_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_thread_id')->constrained('hub_threads')->cascadeOnDelete();
            $table->string('space_uid', 12)->index();
            $table->unsignedBigInteger('author_user_id')->nullable()->index();
            $table->string('question', 200);
            $table->boolean('allow_multiple')->default(false);
            $table->timestamps();

            $table->unique('hub_thread_id');
        });

        Schema::create('hub_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_poll_id')->constrained('hub_polls')->cascadeOnDelete();
            $table->string('label', 80);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('hub_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_poll_id')->constrained('hub_polls')->cascadeOnDelete();
            $table->foreignId('hub_poll_option_id')->constrained('hub_poll_options')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();

            $table->unique(['hub_poll_option_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hub_poll_votes');
        Schema::dropIfExists('hub_poll_options');
        Schema::dropIfExists('hub_polls');
    }
};
