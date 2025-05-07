<?php

namespace App\Services;

use App\Models\Mate;
use App\Models\Space;
use App\Models\InboxMateMate;
use App\Models\InboxMateSpace;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Log;

class MateService
{
    protected $spaceController;

    public function __construct(SpaceController $spaceController)
    {
        $this->spaceController = $spaceController;
    }

    public function getMateWithBookmarks($userId)
    {
        Log::info('🔥 getMateWithBookmarks was called', ['userId' => $userId]);

        $mate = Mate::with(['bookmarks' => function ($query) {
            $query->select('user_id', 'space_uid');
        }])->where('user_id', $userId)->first();

        if (!$mate) return null;

        $mate->bookmarks = collect($mate->bookmarks)->pluck('space_uid');

        // ✅ Spaces the user joined (coliving), but these should NOT be used for inbox red dot
        $joinedSpaces = $this->spaceController->myColivingSpaces();
        $mate->myColivingSpaces = $joinedSpaces[0];

        // ✅ Spaces the user OWNS
        $ownedSpaces = Space::where('user_id', $userId)->pluck('uid')->toArray();

        $userUid = $mate->uid;
        $validUserUids = Mate::pluck('uid')->toArray();
        $validSpaceUids = Space::pluck('uid')->toArray();

        // ✅ PART 1: Personal (mate-to-mate)
        $hasUnreadMateMate = InboxMateMate::where(function ($q) use ($userUid) {
            $q->where(function ($sub) use ($userUid) {
                $sub->where('user1', $userUid)
                    ->where(function ($cond) {
                        $cond->whereNull('user1_checked')
                            ->orWhereRaw('updated_at > user1_checked');
                    });
            })->orWhere(function ($sub) use ($userUid) {
                $sub->where('user2', $userUid)
                    ->where(function ($cond) {
                        $cond->whereNull('user2_checked')
                            ->orWhereRaw('updated_at > user2_checked');
                    });
            });
        })
        ->where(function ($q) use ($userUid, $validUserUids) {
            $q->where('user1', $userUid)->whereIn('user2', $validUserUids)
              ->orWhere('user2', $userUid)->whereIn('user1', $validUserUids);
        })
        ->exists();

        // ✅ PART 2: User’s personal convo with a space (stored in inbox_mate_space)
        $hasUnreadSpaceConvo = InboxMateSpace::where('user1', $userUid)
            ->where(function ($q) {
                $q->whereNull('user1_checked')
                  ->orWhereRaw('updated_at > user1_checked');
            })
            ->whereIn('user2', $validSpaceUids)
            ->exists();

        // 🔴 Combine both for Personal inbox red dot
        $mate->hasUnreadPersonal = $hasUnreadMateMate || $hasUnreadSpaceConvo;

        // ✅ SPACES UNREAD (shown in "My Spaces" for space *owners*)
        $mate->hasUnreadSpaces = InboxMateSpace::whereIn('user2', $ownedSpaces)
            ->where(function ($q) {
                $q->whereNull('user2_checked')
                  ->orWhereRaw('updated_at > user2_checked');
            })
            ->whereIn('user1', $validUserUids)
            ->whereIn('user2', $validSpaceUids)
            ->exists();

        return $mate;
    }
}
