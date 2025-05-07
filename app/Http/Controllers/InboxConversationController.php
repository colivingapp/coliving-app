<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Mate;

use App\Models\InboxMateMate;
use App\Models\InboxMateSpace;


use Illuminate\Http\Request;

class InboxConversationController extends Controller
{

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
                    $query->orWhere('user2', $space->uid);
                }
            });
    
            $conversations = $query->get();
    
            // var_dump($conversations);

            foreach ($conversations as $key => $conversation) {
                $conversations[$key]['participants'] = new \stdClass();

                $conversations[$key]['type'] = 'space_mate';

                // $conversations[$key]['participants']->sender = $conversation['user1'];
                // $conversations[$key]['participants']->sender_avatar_type = 'multiavatar';
                // $conversations[$key]['participants']->sender_avatar = Mate::where('uid', $conversation['user1'])->value('avatar');
    
                $conversations[$key]['participants']->creator = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();
    
                $receiver = Space::where('uid', $conversation['user2'])->select('name', 'photo')->first();

                if ($receiver) {
                    $conversations[$key]['participants']->receiver = $receiver;
                }
                else {
                    unset($conversations[$key]);
                }

            }
                
        }

        // PERSONAL

        $conversations_mate_pov = InboxMateMate::where('user1', $user_uid)->orWhere('user2', $user_uid)->get();

        $key = 0;
        foreach ($conversations_mate_pov as $conversation) {
            $conversations_mate_pov[$key]['participants'] = new \stdClass();

            $conversations_mate_pov[$key]['participants']->creator = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();

            if ($conversation['user1'] != $user_uid) {
                $conversations_mate_pov[$key]['participants']->receiver = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();
            }
            else {
                $conversations_mate_pov[$key]['participants']->receiver = Mate::where('uid', $conversation['user2'])->select('avatar', 'username', 'name', 'uid')->first();
            }
            $conversations_mate_pov[$key]['type'] = 'mate_mate';
            $key++;
        }

        $conversations_mate_space = InboxMateSpace::where('user1', $user_uid)->get();

        $key = 0;
        foreach ($conversations_mate_space as $conversation) {
            $conversations_mate_space[$key]['participants'] = new \stdClass();

            $conversations_mate_space[$key]['participants']->creator = Mate::where('uid', $conversation['user1'])->select('avatar', 'username', 'name', 'uid')->first();

            // $conversations_mate_space[$key]['participants']->receiver = Space::where('uid', $conversation['user2'])->select('name', 'photo')->first();

            $receiver = Space::where('uid', $conversation['user2'])->select('name', 'photo')->first();

            if ($receiver) {
                $conversations_mate_space[$key]['participants']->receiver = $receiver;
                $conversations_mate_space[$key]['type'] = 'space_mate';
            }
            else {
                unset($conversations_mate_space[$key]);
            }

            $key++;
        }

        $conversations_mate_pov = array_merge($conversations_mate_pov->toArray(), $conversations_mate_space->toArray());


        // Return the conversation data as a JSON response
        return response()->json([
            'conversations' => $conversations,
            'conversations_mate_pov' => $conversations_mate_pov
        ], 200);

    }
    
}
