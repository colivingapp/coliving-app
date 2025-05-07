<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            Schema::table('spaces', function (Blueprint $table) {
                $table->string('whatsapp', 191)->after('phone');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            Schema::table('spaces', function (Blueprint $table) {
                $table->dropColumn('whatsapp');
            });
        });
    }
};
