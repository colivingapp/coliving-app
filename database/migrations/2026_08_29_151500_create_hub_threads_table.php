<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hub_threads', function (Blueprint $table) {
            $table->id();
            $table->string('space_uid', 12)->index();
            $table->unsignedBigInteger('author_user_id')->nullable()->index();
            $table->string('title');
            $table->string('type')->default('discussion')->index();
            $table->boolean('is_sticky')->default(false)->index();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('last_post_at')->nullable()->index();
            $table->timestamps();

            $table->index(['space_uid', 'is_sticky', 'last_post_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hub_threads');
    }
};
