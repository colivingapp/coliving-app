<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('settings_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('new_conversation_started')->default(true);
            $table->boolean('new_join_request_received')->default(true);
            $table->boolean('new_review_received')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings_notifications');
    }
}
