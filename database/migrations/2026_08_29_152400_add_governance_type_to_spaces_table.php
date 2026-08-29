<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('governance_type')->default('hosted')->after('status');
        });
    }

    public function down()
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('governance_type');
        });
    }
};
