<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->invertLifestyleAxes();
    }

    public function down(): void
    {
        $this->invertLifestyleAxes();
    }

    private function invertLifestyleAxes(): void
    {
        foreach (['mates', 'spaces'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (['expTrad', 'multiLocal'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->whereNotNull($column)
                    ->update([$column => DB::raw("10 - {$column}")]);
            }
        }
    }
};
