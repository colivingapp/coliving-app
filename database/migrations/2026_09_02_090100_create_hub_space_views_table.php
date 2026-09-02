<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('hub_space_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('space_uid');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('topics_seen_at')->nullable();
            $table->timestamp('members_seen_at')->nullable();
            $table->timestamp('requests_seen_at')->nullable();
            $table->timestamp('calendar_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'space_uid']);
            $table->index('space_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('hub_space_views');
    }
};
