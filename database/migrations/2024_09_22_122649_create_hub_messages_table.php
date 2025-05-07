<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHubMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hub_messages', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('space_uid', 12); // 12-character UID of the space
            $table->string('sender_uid', 12); // 12-character UID of the sender
            $table->unsignedBigInteger('sender_id');
            $table->text('content'); // Content of the message
            $table->timestamps(); // Created and updated timestamps

            // Indexes for performance (optional)
            $table->index('sender_uid');
            $table->index('space_uid');
            $table->index('sender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hub_messages');
    }
}
