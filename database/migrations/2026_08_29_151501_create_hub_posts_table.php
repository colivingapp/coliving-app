<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hub_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_thread_id')->constrained('hub_threads')->cascadeOnDelete();
            $table->string('space_uid', 12)->index();
            $table->unsignedBigInteger('author_user_id')->nullable()->index();
            $table->string('author_uid', 12)->nullable()->index();
            $table->text('content')->nullable();
            $table->timestamps();

            $table->index(['hub_thread_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hub_posts');
    }
};
