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
        Schema::create('join_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mate_id')->index('join_requests_mate_id_foreign');
            $table->string('mate_uid')->nullable();
            $table->string('space_uid')->nullable()->index('join_requests_space_uid_foreign');
            $table->unsignedBigInteger('message_id')->index('join_requests_message_id_foreign');
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('join_requests');
    }
};
