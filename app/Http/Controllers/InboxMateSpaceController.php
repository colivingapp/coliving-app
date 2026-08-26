<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InboxMessage;
use App\Models\Space;
use App\Models\Mate;
use App\Models\InboxMateSpace;
use App\Models\Log;

use App\Services\ChatAuthorization;
use App\Services\EmailService;

use Illuminate\Http\Request;

class InboxMateSpaceController extends Controller
{

    public function store(Request $request, $uid) {

        $content = ChatAuthorization::validatedContent($request);

        $sender = $request->user();
        $sender_id = $sender->id;
        $senderMate = Mate::where('user_id', $sender_id)->select('uid', 'name', 'username')->first();
        if (!$senderMate) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        $sender_uid = $senderMate->uid;

        $space = ChatAuthorization::spaceByUid($uid);
        if (!$space) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $conversation = InboxMateSpace::where('user2', $uid)->where('user1', $sender_uid)->first();
        $newConversation = false;

        // If the conversation does not exist, create it
        if (!$conversation) {
            $newConversation = true;
            $conv_uid = uniqid();
            
            $conversation = InboxMateSpace::create([
                'uid' => $conv_uid,
                'user2' => $uid,
                'user1' => $sender_uid
            ]);
 
            Log::create([
                'user_id' => $sender_id,
                'space_uid' => $uid,
                'other_uid' => $conv_uid,
                'action' => 'new convo mate-space',
            ]);
        }
        else {
            $conv_uid = $conversation->uid;
        }

        ChatAuthorization::touchConversation($conversation, $sender_uid);
    
        $message = InboxMessage::create([
            'conversation_uid' => $conv_uid,
            'sender_uid' => $sender_uid,
            'content' => $content,
        ]);

        if ($newConversation) {
            $user = User::with('settingsNotification')->find($space->user_id);
            $notificationSettings = $user ? $user->settingsNotification : null;

            if ($notificationSettings && $notificationSettings->new_conversation_started) {
                $to = $space->email ?: $user->email;
                $subj = 'New conversation started';
                $template = 'new_conversation_space';
                $from_name = $senderMate->name ?: $sender->name;

                $data = [
                    'name' => $space->name,
                    'uid' => $uid,
                    'request_message' => $content,
                    'from_name' => $from_name,
                    'from_email' => $sender->email,
                    'from_profile_url' => 'https://coliving.app/mate/'.$sender_uid,
                ];

                $emailService = new EmailService();
                $emailService->sendEmail($to, $subj, $template, $data);

                Log::create([
                    'user_id' => $sender_id,
                    'user_uid' => $sender_uid,
                    'space_uid' => $uid,
                    'other_uid' => (string)$message->id,
                    'action' => 'space message email sent',
                ]);
            }
        }
    
        // Retrieve the updated conversation
        $conversation = InboxMessage::where('conversation_uid', $conv_uid)
            ->with('joinRequest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    
        $participants[$sender_uid] = Mate::where('uid', $sender_uid)->select('name', 'username', 'avatar')->first();
        $participants[$sender_uid]['type'] = 'mate';
        $participants[$uid] = Space::where('uid', $uid)->select('name', 'photo')->first();
        $participants[$uid]['type'] = 'space';
        
        return response()->json([
            'conversation' => $conversation,
            'participants' => $participants
        ], 201);
    }

    public function storeSpacePOV(Request $request, $uid, $mate_uid) {

        $content = ChatAuthorization::validatedContent($request);

        $space = ChatAuthorization::ownedSpace($request->user(), $uid);
        if (!$space) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $sender_uid = $uid;
        $mate = Mate::where('uid', $mate_uid)->first();
        if (!$mate) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $conversation = InboxMateSpace::where('user2', $uid)->where('user1', $mate_uid)->first();
    
        if (!$conversation) {
            $conv_uid = uniqid();

            $conversation = InboxMateSpace::create([
                'uid' => $conv_uid,
                'user2' => $uid,
                'user1' => $mate_uid
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
    
        return response()->json([
            'conversation' => $conversation
        ], 201);
    }

    public function showConversationSpacePOV(Request $request, $space_uid, $mate_uid) {

        $space = ChatAuthorization::spaceByUid($space_uid);
        if (!$space) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!ChatAuthorization::ownedSpace($request->user(), $space_uid)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $participants = [];
        $participants[$mate_uid] = Mate::where('uid', $mate_uid)->select('name', 'username', 'avatar')->first();
        $participants[$mate_uid]['type'] = 'mate';
        $participants[$space_uid] = Space::where('uid', $space_uid)->select('name', 'photo')->first();
        $participants[$space_uid]['type'] = 'space';

        $convo = InboxMateSpace::where('user2', $space_uid)->where('user1', $mate_uid)->first();

        if (!$convo) {
            return response()->json([
                'conversation' => [],
                'participants' => $participants
            ], 200);
        }

        $convo->user2_checked = now();
        $convo->timestamps = false;
        $convo->save();
        $convo->timestamps = true;

        $conversation = InboxMessage::where('conversation_uid', $convo->uid)
            ->with('joinRequest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'conversation' => $conversation,
            'participants' => $participants
        ], 200);
    }

    public function showConversation(Request $request, $uid) {

        $sender_id = $request->user()->id;
        $sender_uid =  Mate::where('user_id', $sender_id)->value('uid');
        $conversation_uid = '';

        $participants = [];

        $convo = InboxMateSpace::where('user2', $uid)->where('user1', $sender_uid)->first();

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
            ->with('joinRequest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        }

        $participants[$sender_uid] = Mate::where('uid', $sender_uid)->select('name', 'username', 'profile_pic', 'photo', 'avatar')->first();
        $participants[$sender_uid]['type'] = 'mate';
        $participants[$uid] = Space::where('uid', $uid)->select('name', 'photo', 'claimed')->first();
        $participants[$uid]['type'] = 'space';

    
        return response()->json([
            'conversation' => $conversation ?: [],
            'participants' => $participants
        ], 200);
    }
    
    public function showConversations(Request $request) {

        $user_id = $request->user()->id;
        $user_uid = Mate::where('user_id', $user_id)->value('uid');

        // MY SPACES

        // get all spaces owned by the user and use them in loop to prepare db query
        $spaces = Space::where('user_id', $user_id)->select('uid')->get();
        $conversations = [];

        if(sizeof($spaces) > 0) {
            $query = InboxMateSpace::query();

            $query->where(function ($query) use ($spaces) {
                foreach ($spaces as $space) {
                    $query->where('user2', $space->uid);
                }
            });
    
            $conversations = $query->get();
    
            foreach ($conversations as $key => $conversation) {
                $conversations[$key]['participants'] = new \stdClass();
    
                $conversations[$key]['participants']->creator = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();
    
                if ($conversation['space_uid']) {
                    $conversations[$key]['participants']->receiver = Space::where('uid', $conversation['user2'])->select('name', 'photo')->first();
    
                    $conversations[$key]['type'] = 'space_mate';
                }
            }
                
        }

        // Return the conversation data as a JSON response
        return response()->json([
            'conversations' => $conversations
        ], 200);
    }

}
