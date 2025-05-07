<?php

namespace App\Http\Controllers;

use App\Models\HubMessage;
use App\Models\Space;
use App\Models\Mate;

use Illuminate\Http\Request;

class InboxHubController extends Controller
{

    public function store(Request $request, $uid) {

        $sender_id = $request->user()->id;
        $sender_uid = Mate::where('user_id', $sender_id)->value('uid');

        // @TODO: Check if user has joined the space
        // 
        //
        //
    
        // Insert the new message
        $message = HubMessage::create([
            'space_uid' => $uid,
            'sender_id' => $sender_id,
            'sender_uid' => $sender_uid,
            'content' => $request->content,
        ]);
    
        // Retrieve the updated conversation
        $conversation = HubMessage::where('space_uid', $uid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'conversation' => $conversation
        ], 201);

    }

    public function showConversation(Request $request, $uid) {

        $conversation = HubMessage::where('space_uid', $uid)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
        return response()->json([
            'conversation' => $conversation,
        ], 201);
    }
    
}
