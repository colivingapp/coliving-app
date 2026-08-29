<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hub_events', function (Blueprint $table) {
            $table->id();
            $table->string('space_uid', 12)->index();
            $table->unsignedBigInteger('author_user_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hub_events');
    }
};
