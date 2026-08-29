<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hub_events', function (Blueprint $table) {
            $table->string('kind', 20)->default('event')->after('author_user_id')->index();
            $table->date('start_date')->nullable()->after('description')->index();
            $table->date('end_date')->nullable()->after('start_date')->index();
        });
    }

    public function down()
    {
        Schema::table('hub_events', function (Blueprint $table) {
            $table->dropColumn(['kind', 'start_date', 'end_date']);
        });
    }
};
