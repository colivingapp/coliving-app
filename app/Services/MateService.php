<?php

namespace App\Services;

use App\Models\Mate;
use App\Models\Space;
use App\Models\InboxMateMate;
use App\Models\InboxMateSpace;
use App\Http\Controllers\SpaceController;

class MateService
{
    protected $spaceController;

    public function __construct(SpaceController $spaceController)
    {
        $this->spaceController = $spaceController;
    }

    public function getMateWithBookmarks($userId)
    {
        $mate = Mate::with(['bookmarks' => function ($query) {
            $query->select('user_id', 'space_uid');
        }])->where('user_id', $userId)->first();

        if (!$mate) return null;

        $mate->bookmarks = collect($mate->bookmarks)->pluck('space_uid');

        $joinedSpaces = $this->spaceController->myColivingSpaces();
        // Include vacant admin spaces so hub access checks still see owned empty spaces
        $mate->myColivingSpaces = collect($joinedSpaces[0] ?? [])
            ->merge($joinedSpaces[2] ?? [])
            ->unique('space_uid')
            ->values();

        $ownedSpaces = Space::where('user_id', $userId)->pluck('uid');
        $userUid = $mate->uid;

        $hasUnreadMateMate = InboxMateMate::where(function ($q) use ($userUid) {
            $q->where(function ($sub) use ($userUid) {
                $sub->where('user1', $userUid)
                    ->where(function ($cond) {
                        $cond->whereNull('user1_checked')
                            ->orWhereColumn('updated_at', '>', 'user1_checked');
                    })
                    ->whereExists(function ($exists) {
                        $exists->selectRaw('1')
                            ->from('mates')
                            ->whereColumn('mates.uid', 'inbox_mate_mate.user2');
                    });
            })->orWhere(function ($sub) use ($userUid) {
                $sub->where('user2', $userUid)
                    ->where(function ($cond) {
                        $cond->whereNull('user2_checked')
                            ->orWhereColumn('updated_at', '>', 'user2_checked');
                    })
                    ->whereExists(function ($exists) {
                        $exists->selectRaw('1')
                            ->from('mates')
                            ->whereColumn('mates.uid', 'inbox_mate_mate.user1');
                    });
            });
        })->exists();

        $hasUnreadSpaceConvo = InboxMateSpace::where('user1', $userUid)
            ->where(function ($q) {
                $q->whereNull('user1_checked')
                    ->orWhereColumn('updated_at', '>', 'user1_checked');
            })
            ->whereExists(function ($exists) {
                $exists->selectRaw('1')
                    ->from('spaces')
                    ->whereColumn('spaces.uid', 'inbox_mate_space.user2');
            })
            ->exists();

        $mate->hasUnreadPersonal = $hasUnreadMateMate || $hasUnreadSpaceConvo;

        $mate->hasUnreadSpaces = $ownedSpaces->isEmpty()
            ? false
            : InboxMateSpace::whereIn('user2', $ownedSpaces)
                ->where(function ($q) {
                    $q->whereNull('user2_checked')
                        ->orWhereColumn('updated_at', '>', 'user2_checked');
                })
                ->whereExists(function ($exists) {
                    $exists->selectRaw('1')
                        ->from('mates')
                        ->whereColumn('mates.uid', 'inbox_mate_space.user1');
                })
                ->exists();

        return $mate;
    }
}
