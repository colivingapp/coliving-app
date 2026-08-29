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
use App\Models\JoinRequestVote;

use App\Services\ChatAuthorization;
use App\Services\EmailService;
use App\Models\Log;

class InboxJoinRequestController extends Controller
{
    public function store(Request $request, $uid)
    {
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

        $conversation = InboxMateSpace::where('user1', $sender_uid)
            ->whereIn('user2', array_values(array_unique([$space->uid, $uid])))
            ->orderByDesc('id')
            ->first();
        
        if (!$conversation) {
            $conv_uid = uniqid();

            $conversation = InboxMateSpace::create([
                'uid' => $conv_uid,
                'user2' => $space->uid,
                'user1' => $sender_uid
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

        if ($conversation->user2 !== $space->uid) {
            $conversation->user2 = $space->uid;
            $conversation->save();
        }

        InboxJoinRequest::where('mate_id', $sender_id)
            ->where('space_uid', $space->uid)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // store join request
        $joinRequest = new InboxJoinRequest;
        $joinRequest->mate_id = $sender_id;
        $joinRequest->mate_uid = $sender_uid;
        $joinRequest->space_uid = $space->uid;
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
        $participants[$space->uid] = [
            'uid' => $space->uid,
            'username' => $space->username,
            'name' => $space->name,
            'photo' => $space->photo,
            'type' => 'space',
        ];
        if ($uid !== $space->uid) {
            $participants[$uid] = $participants[$space->uid];
        }
     
        $space_name = $space->name;
        $space_user_id = $space->user_id;

        $subj = 'New join request received';
        $subj_admin = '';
        $notifyAdmin = false;

        // if space is not claimed
        if (!$space->claimed) {
            $template = 'join_request_unclaimed';

            $to = $space->email;

                if ($to == 'spaces@coliving.app') {
                    $subj_admin = '[unclaimed_no_email] ';
                }
                else {
                    $subj_admin = '[unclaimed] ';
                }
                $notifyAdmin = true;
        }
        // if space is claimed
        else {
            $template = 'join_request';

            $to = $space->email;
            $notifyAdmin = true;
        }

        $from_name = $senderMate->name ?: $sender->name;

        $data = [
            'name' => $space_name,
            'uid' => $space->uid,
            'request_message' => $content,
            'from_name' => $from_name,
            'from_email' => $sender->email,
            'from_profile_url' => 'https://coliving.app/mate/'.$sender_uid,
        ];

        $emailService = new EmailService();

        $emailService->sendEmail($sender->email, 'Your join request was sent', 'join_request_confirmation', [
            'name' => $space_name,
            'uid' => $space->uid,
            'claimed' => (bool)$space->claimed,
            'request_message' => $content,
        ]);

        $user = User::with('settingsNotification')->find($space_user_id);
        $notificationSettings = $user ? $user->settingsNotification : null;

        if ($notificationSettings && $notificationSettings->new_join_request_received) {
            $emailService->sendEmail($to, $subj, $template, $data);

            Log::create([
                'user_id' => $sender_id,
                'user_uid' => $sender_uid,
                'space_uid' => $space->uid,
                'other_uid' => (string)$joinRequest->id,
                'action' => 'join request email sent',
            ]);
        }

        if ($notifyAdmin) {
            $emailService->sendEmail(env('ADMIN_EMAIL'), $subj_admin.$subj, $template, $data);
        }

        Log::create([
            'user_id' => $sender_id,
            'user_uid' => $sender_uid,
            'space_uid' => $space->uid,
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
        $jr = InboxJoinRequest::where('id', $id)->first();
        if (empty($jr)) {
            return response()->json(['message' => 'Join request does not exist'], 404);
        }

        $space = Space::where('uid', $jr->space_uid)->first();
        if (!$space) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $canDeclineDirectly = ChatAuthorization::isHubAdmin($request->user(), $space);
        $canVote = ChatAuthorization::canVoteOnJoinRequests($request->user(), $space);
        if (!$canDeclineDirectly && !$canVote) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($jr->mate_uid !== $mate_uid) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($jr->status !== 'pending') {
            return response()->json(['message' => 'Join request is not pending'], 409);
        }

        if ($canVote) {
            if ((int) $jr->mate_id === (int) $request->user()->id) {
                return response()->json(['message' => 'You cannot vote on your own join request'], 403);
            }

            JoinRequestVote::updateOrCreate(
                [
                    'join_request_id' => $jr->id,
                    'user_id' => $request->user()->id,
                ],
                ['vote' => 'decline']
            );

            $votesCount = JoinRequestVote::where('join_request_id', $jr->id)
                ->where('vote', 'decline')
                ->count();
            $votesRequired = intdiv(count(ChatAuthorization::activeHubMemberUserIds($space)), 2) + 1;

            if ($votesCount < $votesRequired) {
                return response()->json([
                    'message' => 'vote_recorded',
                    'vote' => 'decline',
                    'decline_votes_count' => $votesCount,
                    'votes_required' => $votesRequired,
                ], 200);
            }
        }

        $jr->status = 'declined';
        $jr->save();

        $to = User::where('id', $jr->mate_id)->value('email');
        $emailService = new EmailService();
        $emailService->sendEmail($to, 'Join Request Declined', 'join_request_declined', [
            'name' => $space->name,
            'uid' => $jr->space_uid
        ]);

        Log::create([
            'user_id' => $jr->mate_id,
            'user_uid' => $mate_uid,
            'space_uid' => $jr->space_uid,
            'action' => 'join request declined',
        ]);

        return response()->json(['message' => 'success'], 200);
    }
    
    public function accept(Request $request, $id)
    {
        $jr = InboxJoinRequest::where('id', $id)->first();
        if (empty($jr)) {
            return response()->json(['message' => 'Join request does not exist'], 404);
        }

        $space = Space::where('uid', $jr->space_uid)->select('user_id', 'governance_type', 'name', 'uid')->first();
        if (!$space) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!ChatAuthorization::isHubAdmin($request->user(), $space) && !ChatAuthorization::canVoteOnJoinRequests($request->user(), $space)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($jr->status !== 'pending') {
            return response()->json(['message' => 'Join request is not pending'], 409);
        }

        if (ChatAuthorization::canVoteOnJoinRequests($request->user(), $space)) {
            if ((int) $jr->mate_id === (int) $request->user()->id) {
                return response()->json(['message' => 'You cannot vote on your own join request'], 403);
            }

            JoinRequestVote::updateOrCreate(
                [
                    'join_request_id' => $jr->id,
                    'user_id' => $request->user()->id,
                ],
                ['vote' => 'accept']
            );

            $votesCount = JoinRequestVote::where('join_request_id', $jr->id)
                ->where('vote', 'accept')
                ->count();
            $votesRequired = intdiv(count(ChatAuthorization::activeHubMemberUserIds($space)), 2) + 1;

            if ($votesCount < $votesRequired) {
                return response()->json([
                    'message' => 'vote_recorded',
                    'vote' => 'accept',
                    'accept_votes_count' => $votesCount,
                    'votes_required' => $votesRequired,
                ], 200);
            }
        }

        $isJoined = SpaceMate::where('space_uid', $jr->space_uid)->where('user_id', $jr->mate_id)->where('status', 'active')->first();
        if (!empty($isJoined)) {
            return response()->json(['message' => 'Already joined'], 409);
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

        return response()->json(['message' => 'success'], 200);
    }
}
