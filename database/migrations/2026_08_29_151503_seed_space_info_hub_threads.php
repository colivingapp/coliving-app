<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $spaces = DB::table('spaces')
            ->select('uid', 'user_id', 'info', 'created_at', 'updated_at')
            ->get();

        foreach ($spaces as $space) {
            $existingThread = DB::table('hub_threads')
                ->where('space_uid', $space->uid)
                ->where('type', 'space_info')
                ->first();

            if ($existingThread) {
                continue;
            }

            $now = now();
            $threadId = DB::table('hub_threads')->insertGetId([
                'space_uid' => $space->uid,
                'author_user_id' => $space->user_id,
                'title' => 'Space Info',
                'type' => 'space_info',
                'is_sticky' => true,
                'is_locked' => false,
                'last_post_at' => $space->updated_at ?: $now,
                'created_at' => $space->created_at ?: $now,
                'updated_at' => $space->updated_at ?: $now,
            ]);

            DB::table('hub_posts')->insert([
                'hub_thread_id' => $threadId,
                'space_uid' => $space->uid,
                'author_user_id' => $space->user_id,
                'author_uid' => DB::table('mates')->where('user_id', $space->user_id)->value('uid'),
                'content' => $space->info,
                'created_at' => $space->created_at ?: $now,
                'updated_at' => $space->updated_at ?: $now,
            ]);
        }
    }

    public function down()
    {
        $threadIds = DB::table('hub_threads')
            ->where('type', 'space_info')
            ->pluck('id');

        DB::table('hub_posts')->whereIn('hub_thread_id', $threadIds)->delete();
        DB::table('hub_threads')->whereIn('id', $threadIds)->delete();
    }
};
