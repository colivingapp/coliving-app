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
        Schema::create('spaces_ratings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('space_uid', 32)->index('mate_ratings_mate_id_foreign');
            $table->unsignedBigInteger('from_user_id')->index('mate_ratings_rated_mate_id_foreign');
            $table->smallInteger('rating');
            $table->string('reference', 300);
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
        Schema::dropIfExists('spaces_ratings');
    }
};
