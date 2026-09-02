<?php

namespace App\Services;

use App\Models\Mate;
use App\Models\Space;
use App\Models\InboxMateMate;
use App\Models\InboxMateSpace;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $joinedSpaces = $this->decorateHubSpacesWithUnseen(
            $this->spaceController->myColivingSpaces(),
            $userId
        );
        // Include vacant admin spaces so hub access checks still see owned empty spaces
        $mate->myColivingSpaces = collect($joinedSpaces[0] ?? [])
            ->merge($joinedSpaces[2] ?? [])
            ->unique('space_uid')
            ->values();
        $mate->hasUnseenHub = $this->hubSpaces($joinedSpaces)->some(fn ($space) => (bool) ($space->hub_unseen ?? false));

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

    public function hasUnseenHubForCurrentUser(): bool
    {
        return $this->hubUnseenSummaryForCurrentUser()['hasUnseenHub'];
    }

    public function hubUnseenSummaryForCurrentUser(?string $spaceUid = null): array
    {
        if (!auth()->check()) {
            return [
                'hasUnseenHub' => false,
                'unseenSections' => [],
            ];
        }

        $joinedSpaces = $this->decorateHubSpacesWithUnseen(
            $this->spaceController->myColivingSpaces(),
            (int) auth()->id()
        );
        $hubSpaces = $this->hubSpaces($joinedSpaces);

        $space = $spaceUid
            ? $hubSpaces->first(fn ($item) => ($item->space_uid ?? null) === $spaceUid)
            : null;

        return [
            'hasUnseenHub' => $hubSpaces->some(fn ($space) => (bool) ($space->hub_unseen ?? false)),
            'unseenSections' => $space->hub_unseen_sections ?? [],
        ];
    }

    public function decorateHubSpacesWithUnseen(array $joinedSpaces, int $userId): array
    {
        $hubSpaces = $this->hubSpaces($joinedSpaces);
        $unseenByUid = $this->hubUnseenMetadataBySpace($hubSpaces, $userId);

        foreach ([0, 1, 2] as $index) {
            $joinedSpaces[$index] = collect($joinedSpaces[$index] ?? [])
                ->map(function ($space) use ($unseenByUid) {
                    $spaceUid = $space->space_uid ?? null;
                    $metadata = $unseenByUid[$spaceUid] ?? [
                        'any' => false,
                        'sections' => [],
                    ];

                    $space->hub_unseen = $metadata['any'];
                    $space->hub_unseen_sections = $metadata['sections'];

                    return $space;
                })
                ->values();
        }

        return $joinedSpaces;
    }

    public function hubUnseenSectionsForCurrentUserSpace(string $spaceUid): array
    {
        if (!auth()->check()) {
            return [];
        }

        return $this->hubUnseenSummaryForCurrentUser($spaceUid)['unseenSections'];
    }

    private function hubSpaces(array $joinedSpaces)
    {
        return collect($joinedSpaces[0] ?? [])
            ->merge($joinedSpaces[2] ?? [])
            ->unique('space_uid')
            ->values();
    }

    private function hubUnseenMetadataBySpace($hubSpaces, int $userId): array
    {
        if ($hubSpaces->isEmpty()) {
            return [];
        }

        $spaceUids = $hubSpaces->pluck('space_uid')->filter()->unique()->values()->all();
        $viewsByUid = Schema::hasTable('hub_space_views')
            ? DB::table('hub_space_views')
                ->where('user_id', $userId)
                ->whereIn('space_uid', $spaceUids)
                ->get()
                ->keyBy('space_uid')
            : collect();

        $activityBySection = [
            'topics' => $this->topicActivityBySpace($spaceUids),
            'members' => $this->memberActivityBySpace($spaceUids),
            'requests' => $this->pendingRequestActivityBySpace($hubSpaces),
            'calendar' => $this->calendarActivityBySpace($spaceUids),
        ];

        $metadata = [];

        foreach ($hubSpaces as $space) {
            $spaceUid = $space->space_uid ?? null;
            if (!$spaceUid) {
                continue;
            }

            $view = $viewsByUid[$spaceUid] ?? null;
            $sections = [];

            foreach ($activityBySection as $section => $activityByUid) {
                $latestActivityAt = $activityByUid[$spaceUid] ?? null;
                if (!$latestActivityAt) {
                    continue;
                }

                $seenColumn = $section.'_seen_at';
                $sectionSeenAt = $view && property_exists($view, $seenColumn)
                    ? $view->{$seenColumn}
                    : null;
                $baseline = $sectionSeenAt
                    ?? $view?->last_seen_at
                    ?? ($space->joined_at ?? null);

                if (!$baseline || strtotime((string) $latestActivityAt) > strtotime((string) $baseline)) {
                    $sections[] = $section;
                }
            }

            $metadata[$spaceUid] = [
                'any' => !empty($sections),
                'sections' => $sections,
            ];
        }

        return $metadata;
    }

    private function topicActivityBySpace(array $spaceUids): array
    {
        if (empty($spaceUids) || !Schema::hasTable('hub_threads')) {
            return [];
        }

        $activity = [];
        $remember = function ($rows) use (&$activity) {
            foreach ($rows as $uid => $at) {
                if ($at && (!isset($activity[$uid]) || $at > $activity[$uid])) {
                    $activity[$uid] = $at;
                }
            }
        };

        $remember(DB::table('hub_threads')
            ->whereIn('space_uid', $spaceUids)
            ->select('space_uid', DB::raw('MAX(GREATEST(
                COALESCE(last_post_at, created_at),
                COALESCE(updated_at, created_at),
                created_at
            )) as last_activity_at'))
            ->groupBy('space_uid')
            ->pluck('last_activity_at', 'space_uid'));

        if (Schema::hasTable('hub_posts')) {
            $remember(DB::table('hub_posts')
                ->whereIn('space_uid', $spaceUids)
                ->select('space_uid', DB::raw('MAX(GREATEST(
                    COALESCE(created_at, updated_at),
                    COALESCE(updated_at, created_at)
                )) as last_activity_at'))
                ->groupBy('space_uid')
                ->pluck('last_activity_at', 'space_uid'));
        }

        return $activity;
    }

    private function memberActivityBySpace(array $spaceUids): array
    {
        if (empty($spaceUids) || !Schema::hasTable('space_mates')) {
            return [];
        }

        return DB::table('space_mates')
            ->whereIn('space_uid', $spaceUids)
            ->select('space_uid', DB::raw('MAX(GREATEST(
                COALESCE(joined_at, created_at),
                COALESCE(left_at, created_at),
                COALESCE(updated_at, created_at)
            )) as last_activity_at'))
            ->groupBy('space_uid')
            ->pluck('last_activity_at', 'space_uid')
            ->all();
    }

    private function calendarActivityBySpace(array $spaceUids): array
    {
        if (empty($spaceUids) || !Schema::hasTable('hub_events')) {
            return [];
        }

        return DB::table('hub_events')
            ->whereIn('space_uid', $spaceUids)
            ->select('space_uid', DB::raw('MAX(GREATEST(
                COALESCE(created_at, updated_at),
                COALESCE(updated_at, created_at)
            )) as last_activity_at'))
            ->groupBy('space_uid')
            ->pluck('last_activity_at', 'space_uid')
            ->all();
    }

    private function pendingRequestActivityBySpace($hubSpaces): array
    {
        if (!Schema::hasTable('join_requests')) {
            return [];
        }

        $actionableSpaceUids = $hubSpaces
            ->filter(fn ($space) => $this->canManageHubRequests($space))
            ->pluck('space_uid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($actionableSpaceUids)) {
            return [];
        }

        return DB::table('join_requests')
            ->whereIn('space_uid', $actionableSpaceUids)
            ->where('status', 'pending')
            ->select('space_uid', DB::raw('MAX(GREATEST(created_at, updated_at)) as last_activity_at'))
            ->groupBy('space_uid')
            ->pluck('last_activity_at', 'space_uid')
            ->all();
    }

    private function canManageHubRequests($space): bool
    {
        return ($space->governance_type ?? null) === Space::GOVERNANCE_SELF_MANAGED
            || ($space->role ?? null) === 'admin';
    }
}
