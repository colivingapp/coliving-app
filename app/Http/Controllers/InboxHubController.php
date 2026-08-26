<?php

namespace App\Http\Controllers;

use App\Models\HubMessage;
use App\Models\Mate;
use App\Services\ChatAuthorization;
use Illuminate\Http\Request;

class InboxHubController extends Controller
{
    public function store(Request $request, $uid)
    {
        $content = ChatAuthorization::validatedContent($request);

        $space = ChatAuthorization::spaceByUid($uid);
        if (!$space) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!ChatAuthorization::canAccessHub($request->user(), $uid)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $sender = Mate::where('user_id', $request->user()->id)->first();
        if (!$sender) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        HubMessage::create([
            'space_uid' => $uid,
            'sender_id' => $request->user()->id,
            'sender_uid' => $sender->uid,
            'content' => $content,
        ]);

        $conversation = HubMessage::where('space_uid', $uid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'conversation' => $conversation,
        ], 201);
    }

    public function showConversation(Request $request, $uid)
    {
        $space = ChatAuthorization::spaceByUid($uid);
        if (!$space) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!ChatAuthorization::canAccessHub($request->user(), $uid)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $conversation = HubMessage::where('space_uid', $uid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'conversation' => $conversation,
        ], 200);
    }
}
