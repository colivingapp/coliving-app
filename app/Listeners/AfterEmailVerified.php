<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\InboxMateSpace;
use App\Models\InboxMessage;
use App\Models\InboxJoinRequest;

use App\Models\SpaceMate;

use App\Models\User;
use App\Models\Mate;
use App\Models\SettingsNotification;

use Illuminate\Support\Facades\Config;

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

        // Join the Example Coliving Space
        $exampleSpaceUid = config('ca.example_space_uid');

        // Create new conversation
        $conv_uid = uniqid();
        $conversation = InboxMateSpace::create([
            'uid' => $conv_uid,
            'user1' => $user_uid,
            'user2' => $exampleSpaceUid
        ]);

        // Insert the new message
        $message = InboxMessage::create([
            'conversation_uid' => $conv_uid,
            'sender_uid' => $user_uid,
            'content' => 'Hello, I want to join your space!',
        ]);

        // Store join request
        $joinRequest = new InboxJoinRequest;
        $joinRequest->mate_id = $user_id;
        $joinRequest->mate_uid = $user_uid;
        $joinRequest->space_uid = $exampleSpaceUid;
        $joinRequest->message_id = $message->id;
        $joinRequest->status = 'accepted';
        $joinRequest->save();

        // Add user to space
        $spaceMate = new SpaceMate;
        $spaceMate->space_uid = $exampleSpaceUid;
        $spaceMate->user_id = $user_id;
        $spaceMate->joined_at = now();
        $spaceMate->status = 'active';
        $spaceMate->role = 'member';
        $spaceMate->save();

    }
}
