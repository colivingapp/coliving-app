<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('hub_space_views')) {
            return;
        }

        Schema::table('hub_space_views', function (Blueprint $table) {
            if (!Schema::hasColumn('hub_space_views', 'topics_seen_at')) {
                $table->timestamp('topics_seen_at')->nullable()->after('last_seen_at');
            }
            if (!Schema::hasColumn('hub_space_views', 'members_seen_at')) {
                $table->timestamp('members_seen_at')->nullable()->after('topics_seen_at');
            }
            if (!Schema::hasColumn('hub_space_views', 'requests_seen_at')) {
                $table->timestamp('requests_seen_at')->nullable()->after('members_seen_at');
            }
            if (!Schema::hasColumn('hub_space_views', 'calendar_seen_at')) {
                $table->timestamp('calendar_seen_at')->nullable()->after('requests_seen_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('hub_space_views')) {
            return;
        }

        Schema::table('hub_space_views', function (Blueprint $table) {
            foreach (['calendar_seen_at', 'requests_seen_at', 'members_seen_at', 'topics_seen_at'] as $column) {
                if (Schema::hasColumn('hub_space_views', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
