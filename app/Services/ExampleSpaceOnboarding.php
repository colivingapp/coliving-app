<?php

namespace App\Services;

use App\Models\InboxJoinRequest;
use App\Models\InboxMateSpace;
use App\Models\InboxMessage;
use App\Models\SpaceMate;
use App\Models\User;

class ExampleSpaceOnboarding
{
    public const WELCOME_MESSAGE = 'Welcome to the Coliving App! Your join request to Example Coliving Space has been automatically accepted. You can find more details about the space in the Hub.';

    public function join(User $user, string $mateUid): void
    {
        $exampleSpaceUid = config('ca.example_space_uid');
        if (! $exampleSpaceUid) {
            return;
        }

        $conversation = InboxMateSpace::create([
            'uid' => uniqid(),
            'user1' => $mateUid,
            'user2' => $exampleSpaceUid,
        ]);

        $requestMessage = InboxMessage::create([
            'conversation_uid' => $conversation->uid,
            'sender_uid' => $mateUid,
            'content' => 'Hello, I want to join your space!',
        ]);

        InboxJoinRequest::create([
            'mate_id' => $user->id,
            'mate_uid' => $mateUid,
            'space_uid' => $exampleSpaceUid,
            'message_id' => $requestMessage->id,
            'status' => 'accepted',
        ]);

        SpaceMate::create([
            'space_uid' => $exampleSpaceUid,
            'user_id' => $user->id,
            'joined_at' => now(),
            'status' => 'active',
            'role' => 'member',
        ]);

        $welcomeSentAt = $requestMessage->created_at->copy()->addSecond();
        $welcomeMessage = new InboxMessage([
            'conversation_uid' => $conversation->uid,
            'sender_uid' => $exampleSpaceUid,
            'content' => self::WELCOME_MESSAGE,
        ]);
        $welcomeMessage->created_at = $welcomeSentAt;
        $welcomeMessage->updated_at = $welcomeSentAt;
        $welcomeMessage->save();

        $conversation->updated_at = $welcomeSentAt;
        $conversation->save();
    }
}
