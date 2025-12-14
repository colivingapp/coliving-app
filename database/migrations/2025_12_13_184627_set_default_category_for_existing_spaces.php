<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set all existing spaces without a category to 'managed-coliving'
        DB::table('spaces')
            ->where(function ($query) {
                $query->whereNull('category')
                      ->orWhere('category', '');
            })
            ->update(['category' => 'managed-coliving']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear category for spaces that were set to managed-coliving by this migration
        // Note: This is a best-effort reversal - we can't distinguish between spaces
        // that were set by this migration vs. spaces that were manually set to managed-coliving
        DB::table('spaces')
            ->where('category', 'managed-coliving')
            ->update(['category' => null]);
    }
};
