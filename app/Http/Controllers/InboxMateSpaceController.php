<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InboxMessage;
use App\Models\Space;
use App\Models\Mate;
use App\Models\InboxMateSpace;
use App\Models\Log;
use App\Models\SettingsNotification;

use App\Services\EmailService;

use Illuminate\Http\Request;

class InboxMateSpaceController extends Controller
{

    public function store(Request $request, $uid) {

        $sender_id = $request->user()->id;
        $sender_uid = Mate::where('user_id', $sender_id)->value('uid');

        $conversation = InboxMateSpace::where('user2', $uid)->where('user1', $sender_uid)->first();
        $space = Space::where('uid', $uid)->first();

        // If the conversation does not exist, create it
        if (!$conversation) {
            $conv_uid = uniqid();
            
            $conversation = InboxMateSpace::create([
                'uid' => $conv_uid,
                'user2' => $uid,
                'user1' => $sender_uid
            ]);
 
            // Add space id to conversations table
            $space_id = $space->id;
            $user_id = $space->user_id;

            $user = User::with('settingsNotification')->findOrFail($user_id);
            $notificationSettings = $user->settingsNotification;

            if ($notificationSettings->new_conversation_started) {
                $user_email = $user->email;

                $to = $user_email;
                $subj = 'New conversation started';
                $template = 'new_conversation_space';
    
                $data = [
                    // 'name' => $space_name,
                    // 'uid' => $uid,
                ];
    
                $emailService = new EmailService();
                $emailService->sendEmail($to, $subj, $template, $data);
            }

            Log::create([
                'user_id' => $sender_id,
                'space_uid' => $uid,
                'other_uid' => $conv_uid,
                'action' => 'new convo mate-space',
            ]);
        }
        else {
            $conv_uid = $conversation['uid'];

            $conversation->updated_at = now();
            $conversation->save();
        }
    
        // Insert the new message
        $message = InboxMessage::create([
            'conversation_uid' => $conv_uid,
            'sender_uid' => $sender_uid,
            'content' => $request->content,
        ]);
    
        // Retrieve the updated conversation
        $conversation = InboxMessage::where('conversation_uid', $conv_uid)
            ->with('joinRequest')
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

    public function storeSpacePOV(Request $request, $uid, $mate_uid) {

        $current_user_id = $request->user()->id;
        $sender_uid = $uid;

        // check if current_user is the owner of the space
        $space = Space::where('user_id', $current_user_id)->where('uid', $uid)->first();
        if (!$space) {
            return('Huh 2?');
        }

        $conversation = InboxMateSpace::where('user2', $uid)->where('user1', $mate_uid)->first();
    
        // If the conversation does not exist, create it
        if (!$conversation) {
            $conv_uid = uniqid();

            // // Add space id to conversations table
            $space_id = Space::where('uid', $uid)->value('id');
            $conversation = InboxMateSpace::create([
                'uid' => $conv_uid,
                'user2' => $uid,
                'user1' => $mate_uid
            ]);
        }
        else {
            $conv_uid = $conversation['uid'];

            $conversation->updated_at = now();
            $conversation->save();
        }
    
        // Insert the new message
        $message = InboxMessage::create([
            'conversation_uid' => $conv_uid,
            'sender_uid' => $sender_uid,
            'content' => $request->content,
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

        $current_user_uid = $space_uid;
        $conversation_uid = '';
        
        $participants = [];

        $conversation_uid = InboxMateSpace::where('user2', $space_uid)
        ->where('user1', $mate_uid)
        ->value('uid');

        $convo = InboxMateSpace::where('user2', $space_uid)->where('user1', $mate_uid)->first();

        if ($convo) {
            if ($current_user_uid == $mate_uid) {
                $convo->user1_checked = now();
            } else {
                $convo->user2_checked = now();
            }
            $convo->timestamps = false;
            $convo->save();
            $convo->timestamps = true;

            $conversation = InboxMessage::where('conversation_uid', $conversation_uid)
            ->with('joinRequest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        }

        $participants[$mate_uid] = Mate::where('uid', $mate_uid)->select('name', 'username', 'avatar')->first();
        $participants[$mate_uid]['type'] = 'mate';
        $participants[$space_uid] = Space::where('uid', $space_uid)->select('name', 'photo')->first();
        $participants[$space_uid]['type'] = 'space';

        if (count($conversation) > 0) {
            return response()->json([
                'conversation' => $conversation,
                'participants' => $participants
            ], 201);
        }
        else {
            return 'Conversation has not been started.';
        }
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

    
        if ($conversation) {
            return response()->json([
                'conversation' => $conversation,
                'participants' => $participants
            ], 201);
        }
        else {
            // return 'Conversation has not been started.';
            return response()->json([
                'conversation' => 'Conversation has not been started.',
                'participants' => $participants
            ], 201);
        }
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
