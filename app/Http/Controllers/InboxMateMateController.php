<?php

namespace App\Http\Controllers;

use App\Models\InboxMessage;
use App\Models\Mate;
use App\Models\User;
use App\Models\InboxMateMate;
use App\Models\Log;

use Illuminate\Http\Request;

use App\Services\ChatAuthorization;
use App\Services\EmailService;

class InboxMateMateController extends Controller
{

    public function store(Request $request, $uid) {

        $content = ChatAuthorization::validatedContent($request);

        $sender_id = $request->user()->id;
        $sender = Mate::where('user_id', $sender_id)->first();
        if (!$sender) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        $sender_uid = $sender->uid;

        if ($uid === $sender_uid) {
            return response()->json(['message' => 'Cannot message yourself'], 422);
        }

        $recipient = Mate::where('uid', $uid)->first();
        if (!$recipient) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!ChatAuthorization::canAccessMateConversationTarget($request->user(), $sender, $recipient)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $conversation = InboxMateMate::where(function ($query) use ($uid, $sender_uid) {
            $query->where('user2', $uid)
                    ->where('user1', $sender_uid);
        })->orWhere(function ($query) use ($uid, $sender_uid) {
            $query->where('user2', $sender_uid)
                    ->where('user1', $uid);
        })->first();
        
        // If the conversation does not exist, create it
        if (!$conversation) {
            $conv_uid = uniqid();

            $conversation = InboxMateMate::create([
                'uid' => $conv_uid,
                'user2' => $uid,
                'user1' => $sender_uid
            ]);

            $user = User::with('settingsNotification')->find($recipient->user_id);
            $notificationSettings = $user?->settingsNotification;

            if ($notificationSettings && $notificationSettings->new_conversation_started) {
                $emailService = new EmailService();
                $emailService->sendEmail($user->email, 'New conversation started', 'new_conversation', []);
            }
       
            Log::create([
                'user_id' => $sender_id,
                'space_uid' => $uid,
                'other_uid' => $conv_uid,
                'action' => 'new convo mate-mate',
            ]);

        }
        else {
            $conv_uid = $conversation->uid;
        }

        ChatAuthorization::touchConversation($conversation, $sender_uid);
    
        InboxMessage::create([
            'conversation_uid' => $conv_uid,
            'sender_uid' => $sender_uid,
            'content' => $content,
        ]);
    
        // Retrieve the updated conversation
        $conversation = InboxMessage::where('conversation_uid', $conv_uid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    
        $participants[$sender_uid] = Mate::where('uid', $sender_uid)->select('name', 'username', 'avatar')->first();
        $participants[$sender_uid]['type'] = 'mate';
        $participants[$uid] = Mate::where('uid', $uid)->select('name', 'username', 'avatar')->first();
        $participants[$uid]['type'] = 'mate';
        
        return response()->json([
            'conversation' => $conversation,
            'participants' => $participants
        ], 201);

    }

    public function showConversation(Request $request, $uid) {

        $sender_id = $request->user()->id;
        $sender = Mate::where('user_id', $sender_id)->first();
        if (!$sender) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        $sender_uid = $sender->uid;
        $conversation_uid = '';

        $recipient = Mate::where('uid', $uid)->first();
        if (!$recipient) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!ChatAuthorization::canAccessMateConversationTarget($request->user(), $sender, $recipient)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $participants = [];

        $convo = InboxMateMate::where(function ($query) use ($uid, $sender_uid) {
            $query->where('user2', $uid)
                    ->where('user1', $sender_uid);
        })->orWhere(function ($query) use ($uid, $sender_uid) {
            $query->where('user2', $sender_uid)
                    ->where('user1', $uid);
        })->first();

        $conversation = null;

        if ($convo) {
            if ($convo->user1 == $sender_uid) {
                $convo->user1_checked = now();
            } else {
                $convo->user2_checked = now();
            }
            $convo->timestamps = false;
            $convo->save();
            $convo->timestamps = true;

            $conversation = InboxMessage::where('conversation_uid', $convo->uid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        }

        $participants[$sender_uid] = Mate::where('uid', $sender_uid)->select('name', 'username', 'profile_pic', 'photo', 'avatar')->first();
        $participants[$sender_uid]['type'] = 'mate';
        $participants[$uid] = Mate::where('uid', $uid)->select('name', 'username', 'profile_pic', 'photo', 'avatar')->first();
        $participants[$uid]['type'] = 'mate';
    
        return response()->json([
            'conversation' => $conversation ?: [],
            'participants' => $participants
        ], 200);
    }
    

    public function showConversations(Request $request) {

        $user_id = $request->user()->id;
        $user_uid = Mate::where('user_id', $user_id)->value('uid');

        $conversations = [];

        // PERSONAL

        $conversations_mate_pov = InboxMateMate::where('user1', $user_uid)->orWhere('user2', $user_uid)->whereNull('space_uid')->get();

        foreach ($conversations_mate_pov as $key => $conversation) {
            $conversations_mate_pov[$key]['participants'] = new \stdClass();

            // $conversations_mate_pov[$key]['participants']->creator = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();

            if ($conversation['user1'] != $user_uid) {
                $conversations_mate_pov[$key]['participants']->receiver = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();
            }
            else {
                $conversations_mate_pov[$key]['participants']->receiver = Mate::where('uid', $conversation['user2'])->select('avatar', 'username', 'name', 'uid')->first();
            }
            $conversations_mate_pov[$key]['type'] = 'mate_mate';

        }

        // Return the conversation data as a JSON response
        return response()->json([
            'conversations_mate_pov' => $conversations_mate_pov
        ], 200);
    }
    
}
