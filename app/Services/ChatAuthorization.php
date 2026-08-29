<?php

namespace App\Services;

use App\Models\Space;
use App\Models\SpaceMate;
use App\Models\User;
use App\Models\Mate;
use App\Models\InboxMateMate;
use Illuminate\Http\Request;

class ChatAuthorization
{
    public const MAX_MESSAGE_LENGTH = 5000;

    public static function validatedContent(Request $request): string
    {
        return $request->validate([
            'content' => 'required|string|max:'.self::MAX_MESSAGE_LENGTH,
        ])['content'];
    }

    public static function touchConversation($conversation, string $senderUid): void
    {
        $conversation->updated_at = now();

        if ($conversation->user1 === $senderUid) {
            $conversation->user1_checked = now();
        } elseif ($conversation->user2 === $senderUid) {
            $conversation->user2_checked = now();
        }

        $conversation->save();
    }

    public static function spaceByUid(string $spaceUid): ?Space
    {
        return Space::where('uid', $spaceUid)->first();
    }

    public static function ownedSpace(User $user, string $spaceUid): ?Space
    {
        return Space::where('uid', $spaceUid)
            ->where('user_id', $user->id)
            ->first();
    }

    public static function isActiveMember(User $user, string $spaceUid): bool
    {
        return SpaceMate::where('space_uid', $spaceUid)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public static function isHubAdmin(User $user, Space $space): bool
    {
        if ((int) $space->user_id === (int) $user->id) {
            return true;
        }

        return SpaceMate::where('space_uid', $space->uid)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('role', 'admin')
            ->exists();
    }

    public static function hubAdminUserIds(Space $space, ?int $exceptUserId = null): array
    {
        $ids = SpaceMate::where('space_uid', $space->uid)
            ->where('status', 'active')
            ->where('role', 'admin')
            ->when($exceptUserId !== null, fn ($query) => $query->where('user_id', '!=', $exceptUserId))
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($exceptUserId === null || (int) $space->user_id !== $exceptUserId) {
            $ids[] = (int) $space->user_id;
        }

        return array_values(array_unique($ids));
    }

    public static function canAccessHub(User $user, string $spaceUid): bool
    {
        $space = self::spaceByUid($spaceUid);
        if (!$space) {
            return false;
        }

        if ((int) $space->user_id === (int) $user->id) {
            return true;
        }

        return self::isActiveMember($user, $spaceUid);
    }

    public static function mateConversationExists(string $userUid, string $otherUid): bool
    {
        return InboxMateMate::where(function ($query) use ($userUid, $otherUid) {
            $query->where('user1', $userUid)
                ->where('user2', $otherUid);
        })->orWhere(function ($query) use ($userUid, $otherUid) {
            $query->where('user1', $otherUid)
                ->where('user2', $userUid);
        })->exists();
    }

    public static function canAccessMateConversationTarget(User $user, Mate $currentMate, Mate $targetMate): bool
    {
        if ((int) $targetMate->user_id === (int) $user->id) {
            return true;
        }

        if ((int) $targetMate->private === 0) {
            return true;
        }

        return self::mateConversationExists($currentMate->uid, $targetMate->uid);
    }

    public static function safeHttpUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return '';
        }

        return $url;
    }
}
