<?php

namespace App\Http\Controllers;

use App\Models\HubEvent;
use App\Models\HubMessage;
use App\Models\HubPost;
use App\Models\HubThread;
use App\Models\Log;
use App\Models\Mate;
use App\Models\Space;
use App\Models\SpaceMate;
use App\Services\ChatAuthorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HubController extends Controller
{
    private function accessibleSpace(Request $request, string $uid): ?Space
    {
        $space = ChatAuthorization::spaceByUid($uid);
        if (!$space || !ChatAuthorization::canAccessHub($request->user(), $space->uid)) {
            return null;
        }

        return $space;
    }

    private function isAdmin(Request $request, Space $space): bool
    {
        return ChatAuthorization::isHubAdmin($request->user(), $space);
    }

    public function makeAdmin(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || !$this->isAdmin($request, $space)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $targetUserId = (int) $data['user_id'];
        if ($targetUserId === (int) $request->user()->id) {
            return response()->json(['message' => 'You are already an admin.'], 422);
        }

        $membership = SpaceMate::where('space_uid', $space->uid)
            ->where('user_id', $targetUserId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Only current members can be made admins.'], 422);
        }

        if ($membership->role === 'admin') {
            return response()->json(['ok' => true, 'user_id' => $targetUserId]);
        }

        $membership->role = 'admin';
        $membership->save();

        Log::create([
            'user_id' => $request->user()->id,
            'space_uid' => $space->uid,
            'other_uid' => (string) $targetUserId,
            'action' => 'made hub admin',
        ]);

        return response()->json(['ok' => true, 'user_id' => $targetUserId]);
    }

    public function leaveAdmin(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || !$this->isAdmin($request, $space)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $userId = (int) $request->user()->id;
        $remainingAdminIds = ChatAuthorization::hubAdminUserIds($space, $userId);

        if ($remainingAdminIds === []) {
            return response()->json([
                'message' => 'Make another member an admin before you leave.',
            ], 422);
        }

        DB::transaction(function () use ($space, $userId, $remainingAdminIds) {
            if ((int) $space->user_id === $userId) {
                $space->user_id = $remainingAdminIds[0];
                $space->save();
            }

            $membership = SpaceMate::where('space_uid', $space->uid)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->first();

            if ($membership) {
                $membership->left_at = now();
                $membership->status = 'left';
                $membership->role = 'member';
                $membership->save();
            }
        });

        Log::create([
            'user_id' => $userId,
            'space_uid' => $space->uid,
            'action' => 'left as hub admin',
        ]);

        return response()->json(['ok' => true]);
    }

    private function authorFor(Request $request): array
    {
        $mate = Mate::where('user_id', $request->user()->id)->select('uid')->first();

        return [
            'author_user_id' => $request->user()->id,
            'author_uid' => $mate?->uid,
        ];
    }

    private function ensureSpaceInfoThread(Space $space): HubThread
    {
        $thread = HubThread::where('space_uid', $space->uid)
            ->where('type', 'space_info')
            ->first();

        if (!$thread) {
            $thread = HubThread::create([
                'space_uid' => $space->uid,
                'author_user_id' => $space->user_id,
                'title' => 'Space Info',
                'type' => 'space_info',
                'is_sticky' => true,
                'last_post_at' => now(),
            ]);
        }

        $firstPost = HubPost::where('hub_thread_id', $thread->id)
            ->orderBy('created_at')
            ->first();

        if (!$firstPost) {
            HubPost::create([
                'hub_thread_id' => $thread->id,
                'space_uid' => $space->uid,
                'author_user_id' => $space->user_id,
                'author_uid' => Mate::where('user_id', $space->user_id)->value('uid'),
                'content' => $space->info,
            ]);
        }

        return $thread;
    }

    private function canDeleteThread(Request $request, Space $space, HubThread $thread): bool
    {
        if ($thread->type !== 'discussion') {
            return false;
        }

        $isOwner = (int) $thread->author_user_id === (int) $request->user()->id;

        return $this->isAdmin($request, $space) || $isOwner;
    }

    private function decorateThreads($threads, Request $request, Space $space)
    {
        $threadIds = $threads->pluck('id');
        $postCounts = HubPost::whereIn('hub_thread_id', $threadIds)
            ->select('hub_thread_id', DB::raw('count(*) as posts_count'))
            ->groupBy('hub_thread_id')
            ->pluck('posts_count', 'hub_thread_id');

        $latestPosts = HubPost::whereIn('hub_thread_id', $threadIds)
            ->orderByDesc('created_at')
            ->get()
            ->unique('hub_thread_id')
            ->keyBy('hub_thread_id');

        return $threads->map(function ($thread) use ($postCounts, $latestPosts, $request, $space) {
            $thread->posts_count = (int) ($postCounts[$thread->id] ?? 0);
            $thread->replies_count = max(0, $thread->posts_count - 1);
            $thread->latest_post = $latestPosts[$thread->id] ?? null;
            $thread->is_mine = (int) $thread->author_user_id === (int) $request->user()->id;
            $thread->can_delete = $this->canDeleteThread($request, $space, $thread);
            return $thread;
        });
    }

    private function ensureGeneralThreadFromChat(Space $space): void
    {
        $hasGeneralThread = HubThread::where('space_uid', $space->uid)
            ->where('type', 'general')
            ->exists();

        if ($hasGeneralThread || !HubMessage::where('space_uid', $space->uid)->exists()) {
            return;
        }

        $messages = HubMessage::where('space_uid', $space->uid)
            ->orderBy('created_at')
            ->get();

        $thread = new HubThread();
        $thread->space_uid = $space->uid;
        $thread->author_user_id = $messages->first()?->sender_id;
        $thread->title = 'General';
        $thread->type = 'general';
        $thread->is_sticky = false;
        $thread->last_post_at = $messages->last()?->created_at;
        $thread->created_at = $messages->first()?->created_at ?? now();
        $thread->updated_at = $messages->last()?->updated_at ?? now();
        $thread->save();

        foreach ($messages as $message) {
            $post = new HubPost();
            $post->hub_thread_id = $thread->id;
            $post->space_uid = $space->uid;
            $post->author_user_id = $message->sender_id;
            $post->author_uid = $message->sender_uid;
            $post->content = $message->content;
            $post->created_at = $message->created_at;
            $post->updated_at = $message->updated_at;
            $post->save();
        }
    }

    public function threads(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->ensureSpaceInfoThread($space);
        $this->ensureGeneralThreadFromChat($space);

        $threads = HubThread::where('space_uid', $space->uid)
            ->orderByDesc('is_sticky')
            ->orderByDesc('last_post_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'threads' => $this->decorateThreads($threads, $request, $space),
        ]);
    }

    public function createThread(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:160',
            'content' => 'required|string|max:5000',
        ]);

        $author = $this->authorFor($request);
        $thread = HubThread::create([
            'space_uid' => $space->uid,
            'author_user_id' => $author['author_user_id'],
            'title' => trim($data['title']),
            'type' => 'discussion',
            'is_sticky' => false,
            'last_post_at' => now(),
        ]);

        HubPost::create([
            'hub_thread_id' => $thread->id,
            'space_uid' => $space->uid,
            'author_user_id' => $author['author_user_id'],
            'author_uid' => $author['author_uid'],
            'content' => trim($data['content']),
        ]);

        return response()->json(['thread' => $thread], 201);
    }

    public function showThread(Request $request, string $uid, HubThread $thread)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || $thread->space_uid !== $space->uid) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $posts = HubPost::where('hub_thread_id', $thread->id)
            ->orderBy('created_at')
            ->get();

        $thread->is_mine = (int) $thread->author_user_id === (int) $request->user()->id;
        $thread->can_delete = $this->canDeleteThread($request, $space, $thread);

        return response()->json([
            'thread' => $thread,
            'posts' => $posts,
        ]);
    }

    public function destroyThread(Request $request, string $uid, HubThread $thread)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || $thread->space_uid !== $space->uid) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$this->canDeleteThread($request, $space, $thread)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $thread->delete();

        return response()->json(['ok' => true]);
    }

    public function createPost(Request $request, string $uid, HubThread $thread)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || $thread->space_uid !== $space->uid || $thread->is_locked) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $author = $this->authorFor($request);
        $post = HubPost::create([
            'hub_thread_id' => $thread->id,
            'space_uid' => $space->uid,
            'author_user_id' => $author['author_user_id'],
            'author_uid' => $author['author_uid'],
            'content' => trim($data['content']),
        ]);

        $thread->last_post_at = $post->created_at;
        $thread->save();

        return response()->json(['post' => $post], 201);
    }

    public function updateSpaceInfo(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || !$this->isAdmin($request, $space)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'info' => 'nullable|string|max:5000',
        ]);

        $space->info = (string) ($data['info'] ?? '');
        $space->save();

        $thread = $this->ensureSpaceInfoThread($space);
        $firstPost = HubPost::where('hub_thread_id', $thread->id)
            ->orderBy('created_at')
            ->first();

        $firstPost->content = $space->info;
        $firstPost->save();

        return response()->json([
            'space' => $space,
            'thread' => $thread,
            'post' => $firstPost,
        ]);
    }

    public function events(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $events = HubEvent::where('space_uid', $space->uid)
            ->orderByRaw('COALESCE(start_date, DATE(starts_at)) IS NULL')
            ->orderByRaw('COALESCE(start_date, DATE(starts_at))')
            ->orderBy('starts_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (HubEvent $event) => $this->serializeEvent($event, $request));

        return response()->json(['events' => $events]);
    }

    public function createEvent(Request $request, string $uid)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'kind' => 'nullable|in:event,day_off',
            'title' => 'nullable|string|max:160',
            'description' => 'nullable|string|max:5000',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:160',
        ]);

        $kind = $data['kind'] ?? 'event';
        if ($kind === 'day_off' && !$this->isAdmin($request, $space)) {
            return response()->json(['message' => 'Only the host can mark days off.'], 403);
        }

        if ($kind === 'event' && trim((string) ($data['title'] ?? '')) === '') {
            return response()->json(['message' => 'Add an event title.'], 422);
        }

        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = 'Day off';
        }

        $startsAt = $startDate.' '.($data['start_time'] ?? '12:00').':00';
        $endsAt = $endDate.' '.($data['end_time'] ?? ($data['start_time'] ?? '12:00')).':00';

        $event = HubEvent::create([
            'space_uid' => $space->uid,
            'author_user_id' => $request->user()->id,
            'kind' => $kind,
            'title' => $title,
            'description' => trim((string) ($data['description'] ?? '')),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'location' => trim((string) ($data['location'] ?? '')),
        ]);

        return response()->json(['event' => $this->serializeEvent($event, $request)], 201);
    }

    public function destroyEvent(Request $request, string $uid, HubEvent $event)
    {
        $space = $this->accessibleSpace($request, $uid);
        if (!$space || $event->space_uid !== $space->uid) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $isOwner = (int) $event->author_user_id === (int) $request->user()->id;
        if (!$this->isAdmin($request, $space) && !$isOwner) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $event->delete();

        return response()->json(['ok' => true]);
    }

    private function serializeEvent(HubEvent $event, Request $request): array
    {
        $startDate = $event->start_date?->format('Y-m-d')
            ?: ($event->starts_at?->format('Y-m-d'));
        $endDate = $event->end_date?->format('Y-m-d')
            ?: ($event->ends_at?->format('Y-m-d') ?: $startDate);

        return [
            'id' => $event->id,
            'space_uid' => $event->space_uid,
            'author_user_id' => $event->author_user_id,
            'kind' => $event->kind ?: 'event',
            'title' => $event->title,
            'description' => $event->description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'starts_at' => $event->starts_at,
            'ends_at' => $event->ends_at,
            'location' => $event->location,
            'created_at' => $event->created_at,
            'is_mine' => (int) $event->author_user_id === (int) $request->user()->id,
        ];
    }
}
