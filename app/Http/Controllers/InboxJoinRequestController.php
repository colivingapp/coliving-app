<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Mate;
use App\Models\User;
use App\Models\SpaceMate;

use App\Models\InboxMateSpace;
use App\Models\InboxJoinRequest;
use App\Models\InboxMessage;

use App\Models\SettingsNotification;

use App\Services\EmailService;
use App\Models\Log;

class InboxJoinRequestController extends Controller
{
    public function store(Request $request, $uid)
    {
        $sender_id = $request->user()->id;
        $sender_uid = Mate::where('user_id', $sender_id)->value('uid');

        $conversation = InboxMateSpace::where('user2', $uid)->where('user1', $sender_uid)->first();
        
        // If the conversation does not exist, create it
        if (!$conversation) {
            $conv_uid = uniqid();

            $conversation = InboxMateSpace::create([
                'uid' => $conv_uid,
                'user2' => $uid,
                'user1' => $sender_uid
            ]);
        }
        else {
            $conv_uid = $conversation['uid'];

            if ($conversation['user1'] == $sender_uid) {
                $conversation->user1_checked = now();
            } else {
                $conversation->user2_checked = now();
            }
            $conversation->save();
        }

        // Insert the new message
        $message = InboxMessage::create([
            'conversation_uid' => $conv_uid,
            'sender_uid' => $sender_uid,
            'content' => $request->content,
        ]);

        // Update any existing join requests to 'expired'
        InboxJoinRequest::where('mate_id', $sender_id)
        ->where('status', 'pending')
        ->update(['status' => 'expired']);

        // store join request
        $joinRequest = new InboxJoinRequest;
        $joinRequest->mate_id = $sender_id;
        $joinRequest->mate_uid = $sender_uid;
        $joinRequest->space_uid = $uid;
        $joinRequest->message_id = $message->id;
        $joinRequest->status = 'pending';
        $joinRequest->save();

        // Retrieve the updated conversation
        $conversation = InboxMessage::where('conversation_uid', $conv_uid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $participants[$sender_uid] = Mate::where('uid', $sender_uid)->select('name', 'username', 'avatar')->first();
        $participants[$sender_uid]['type'] = 'mate';
        $participants[$uid] = Mate::where('uid', $sender_uid)->select('name', 'username', 'avatar')->first();
        $participants[$uid]['type'] = 'mate';
     
        // $space_name = Space::where('uid', $uid)->value('name');
        $space = Space::where('uid', $uid)->select('name', 'user_id', 'claimed', 'email')->first();
        $space_name = $space->name;
        $space_user_id = $space->user_id;

        $subj = 'New join request received';
        $notifyAdmin = false;

        // if space is not claimed
        if (!$space->claimed) {
            $template = 'join_request_unclaimed';
            // if space doesn't have email, send to admin
            if (!$space->email) {
                $to = env('ADMIN_EMAIL');
            }
            // if space has email, send to space and also notify admin
            else {
                $to = $space->email;
                // $to = env('ADMIN_EMAIL');
                $notifyAdmin = true;
            }
        }
        // if space is claimed
        else {
            $template = 'join_request';

            // if space doesn't have email (maybe later removed from profile), send to admin
            if (!$space->email) {
                $to = env('ADMIN_EMAIL');
            }
            // if space has email, send to space and also notify admin
            else {
                $to = $space->email;
                // $to = env('ADMIN_EMAIL');
                $notifyAdmin = true;
            }
        }

        // $from_name = Mate::where('user_id', $sender_id)->value('name');

        $data = [
            'name' => $space_name,
            'uid' => $uid,
            // 'message' => (string)$request->content,
            // 'from_name' => $from_name,
        ];

        $user = User::with('settingsNotification')->findOrFail($space_user_id);
        $notificationSettings = $user->settingsNotification;

        if ($notificationSettings->new_join_request_received) {
            $emailService = new EmailService();
            $emailService->sendEmail($to, $subj, $template, $data);
        }

        if ($notifyAdmin) {
            $emailService->sendEmail(env('ADMIN_EMAIL'), $subj, $template, $data);
        }

        Log::create([
            'user_id' => $sender_id,
            'user_uid' => $sender_uid,
            'space_uid' => $uid,
            'action' => 'new join request',
        ]);
 
        return response()->json([
            'conversation' => $conversation,
            'joinRequest' => $joinRequest,
            'participants' => $participants
        ], 201);
    }

    public function decline(Request $request, $id, $mate_uid)
    {
        $host_id = $request->user()->id;

        $jr = InboxJoinRequest::where('id', $id)->first();
        if (empty($jr)) { 
            return('join request does not exist'); 
        }

        // return([$host_id, $jr->mate_uid, $mate_uid]);

        if ($jr && $jr->mate_uid == $mate_uid) {
            $jr->status = 'declined';
            $jr->save();
        }

        // Send email
        $space = Space::where('uid', $jr->space_uid)->select('name')->first();

        $to = User::where('id', $jr->mate_id)->value('email');
        $template = 'join_request_declined';

        $data = [
            'name' => $space->name,
            'uid' => $jr->space_uid
        ];

        $emailService = new EmailService();
        $emailService->sendEmail($to, 'Join Request Declined', $template, $data);


        Log::create([
            'user_id' => $jr->mate_id,
            'user_uid' => $mate_uid,
            'space_uid' => $jr->space_uid,
            'action' => 'join request declined',
        ]);

        return('success');
    }
    
    public function accept(Request $request, $id)
    {
        $host_id = $request->user()->id;

        $jr = InboxJoinRequest::where('id', $id)->first();
        if (empty($jr)) { 
            return('join request does not exist'); 
        }

        // $space_user_id = Space::where('uid', $jr->space_uid)->value('user_id');
        $space = Space::where('uid', $jr->space_uid)->select('user_id', 'name')->first();
        $space_user_id = $space->user_id;
        
        $isJoined = SpaceMate::where('space_uid', $jr->space_uid)->where('user_id', $jr->mate_id)->where('status', 'active')->first();
        if (!empty($isJoined)) {
            return('already joined');
        }
            
        if ( $host_id != $space_user_id) {
            return('only the owner can approve join req');
        }

        $jr->status = 'accepted';
        $jr->save();

        // Create a new record in the space_mates table
        $spaceMate = new SpaceMate;
        $spaceMate->space_uid = $jr->space_uid;
        $spaceMate->user_id = $jr->mate_id;
        $spaceMate->joined_at = now();
        $spaceMate->status = 'active';
        $spaceMate->role = 'member';
        $spaceMate->save();

        // Send email
        $to = User::where('id', $jr->mate_id)->value('email');
        $template = 'join_request_accepted';
        $extraData = 'extra_data';

        $data = [
            'name' => $space->name,
            'uid' => $jr->space_uid
        ];

        $emailService = new EmailService();
        $emailService->sendEmail($to, 'Join Request Accepted', $template, $data);


        Log::create([
            'user_id' => $jr->mate_id,
            'space_uid' => $jr->space_uid,
            'action' => 'join request accepted',
        ]);

        return('success');
    }
}
