<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Mate;
use App\Models\SettingsNotification;
use App\Services\ExampleSpaceOnboarding;

class AfterEmailVerified
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // $user = User::find($event->user->id);
        $user = $event->user;
        $user_id = $event->user->id;
        $user_uid = Mate::where('user_id', $user_id)->first()->uid;

        // Set default notification settings
        SettingsNotification::create([
            'user_id' => $user_id,
            'new_conversation_started' => true,
            'new_join_request_received' => true,
            'new_review_received' => true,
        ]);

        app(ExampleSpaceOnboarding::class)->join($user, $user_uid);

    }
}
