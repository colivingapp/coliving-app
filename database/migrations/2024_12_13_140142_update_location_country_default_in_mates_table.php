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
        Schema::table('mates', function (Blueprint $table) {
            $table->string('location_country')->default('none')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mates', function (Blueprint $table) {
            $table->string('location_country')->default(null)->change(); // Revert to no default
        });
    }
};
