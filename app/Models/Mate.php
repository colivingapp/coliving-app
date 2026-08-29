<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'uid',
        'name',
        'private',
        'username',
        'profile_pic',
        'avatar',
        'photo',
        'location_country',
        'twitter',
        'facebook',
        'instagram',
        'youtube',
        'tiktok',
        'pinterest',
        'linkedin',
        'github',
        'substack',
        'medium',
        'hobbies_interests',
        'visited_countries',
        'user_id',
        'orgSpont',
        'quietLoud',
        'earlyNight',
        'workFun',
        'expTrad',
        'multiLocal'
    ];

    // public function bookmarks()
    // {
    //     return $this->hasMany(Bookmark::class, 'user_id');
    // }

    public static function hubSelectColumns(): array
    {
        return [
            'id', 'user_id', 'uid', 'name', 'username', 'avatar', 'profile_pic', 'photo', 'private',
            'location_country', 'hobbies_interests', 'visited_countries',
            'twitter', 'facebook', 'instagram', 'youtube', 'tiktok',
            'pinterest', 'linkedin', 'github', 'substack', 'medium',
            'orgSpont', 'quietLoud', 'earlyNight', 'workFun', 'expTrad', 'multiLocal',
        ];
    }

    public function profileFillScore(): int
    {
        $score = 0;
        $uid = trim((string) $this->uid);
        $name = trim((string) $this->name);
        $username = trim((string) $this->username);

        if ($name !== '' && strcasecmp($name, $uid) !== 0) {
            $score += 2;
        }
        if ($username !== '' && strcasecmp($username, $uid) !== 0) {
            $score += 2;
        }

        $hasPhoto = trim((string) $this->photo) !== '' || (string) $this->profile_pic === 'photo';
        $hasCustomAvatar = trim((string) $this->avatar) !== '' && (string) $this->profile_pic === 'avatar';
        if ($hasPhoto) {
            $score += 3;
        } elseif ($hasCustomAvatar) {
            $score += 1;
        }

        $country = strtolower(trim((string) $this->location_country));
        if ($country !== '' && $country !== 'none') {
            $score += 2;
        }

        $about = trim((string) $this->hobbies_interests);
        if ($about !== '') {
            $score += 2;
            if (mb_strlen($about) >= 80) {
                $score += 2;
            }
            if (mb_strlen($about) >= 200) {
                $score += 2;
            }
        }

        $score += min(count($this->parsedVisitedCountries()), 20);

        foreach (['twitter', 'facebook', 'instagram', 'youtube', 'tiktok', 'pinterest', 'linkedin', 'github', 'substack', 'medium'] as $network) {
            if (trim((string) $this->{$network}) !== '') {
                $score += 2;
            }
        }

        foreach (['orgSpont', 'quietLoud', 'earlyNight', 'workFun', 'expTrad', 'multiLocal'] as $key) {
            if ($this->{$key} === null || $this->{$key} === '') {
                continue;
            }
            if ((int) $this->{$key} === 5) {
                continue;
            }
            $score += 1;
        }

        return $score;
    }

    public static function hubActivityScore(int $threadCount, int $postCount): int
    {
        return ($threadCount * 50) + ($postCount * 15);
    }

    public static function attachHubProfiles($spaceMates, int $viewerUserId): void
    {
        $userIds = collect($spaceMates)->pluck('user_id')->filter()->unique()->values()->all();
        $profiles = static::whereIn('user_id', $userIds)
            ->select(static::hubSelectColumns())
            ->get()
            ->keyBy('user_id');

        foreach ($spaceMates as $spaceMate) {
            $profile = $profiles->get($spaceMate->user_id);
            if ($profile) {
                $spaceMate->profile_fill_score = $profile->profileFillScore();
                $profile->fillHubMember($spaceMate, $viewerUserId);
                $spaceMate->account_deleted = false;
            } else {
                $spaceMate->profile_fill_score = 0;
                $spaceMate->account_deleted = true;
            }
        }
    }

    public function fillHubMember($member, int $viewerUserId): void
    {
        $member->uid = $this->uid;
        $member->name = $this->name;
        $member->username = $this->username;
        $member->avatar = $this->avatar;
        $member->profile_pic = $this->profile_pic;
        $member->photo = $this->photo;
        $member->private = (int) $this->private;

        $canSeeExtras = (int) $this->private === 0 || (int) $this->user_id === $viewerUserId;
        if (! $canSeeExtras) {
            $member->hobbies_interests = '';
            $member->location_country = '';
            $member->visited_countries = [];
            $member->twitter = '';
            $member->facebook = '';
            $member->instagram = '';
            $member->youtube = '';
            $member->tiktok = '';
            $member->pinterest = '';
            $member->linkedin = '';
            $member->github = '';
            $member->substack = '';
            $member->medium = '';
            $member->orgSpont = null;
            $member->quietLoud = null;
            $member->earlyNight = null;
            $member->workFun = null;
            $member->expTrad = null;
            $member->multiLocal = null;
            return;
        }

        $country = strtolower(trim((string) $this->location_country));
        $member->location_country = ($country !== '' && $country !== 'none') ? $country : '';
        $member->hobbies_interests = (string) $this->hobbies_interests;
        $member->visited_countries = $this->parsedVisitedCountries();
        $member->twitter = (string) $this->twitter;
        $member->facebook = (string) $this->facebook;
        $member->instagram = (string) $this->instagram;
        $member->youtube = (string) $this->youtube;
        $member->tiktok = (string) $this->tiktok;
        $member->pinterest = (string) $this->pinterest;
        $member->linkedin = (string) $this->linkedin;
        $member->github = (string) $this->github;
        $member->substack = (string) $this->substack;
        $member->medium = (string) $this->medium;
        $member->orgSpont = $this->orgSpont;
        $member->quietLoud = $this->quietLoud;
        $member->earlyNight = $this->earlyNight;
        $member->workFun = $this->workFun;
        $member->expTrad = $this->expTrad;
        $member->multiLocal = $this->multiLocal;
    }

    public function parsedVisitedCountries(): array
    {
        $raw = $this->visited_countries;
        if (is_array($raw)) {
            return array_values(array_filter($raw, fn ($code) => is_string($code) && $code !== ''));
        }
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($code) => is_string($code) && $code !== ''));
    }

    public function bookmarks() {
        return $this->hasMany(Bookmark::class, 'user_id');
    }

    // public function ratings()
    // {
    //     return $this->hasMany(MateRating::class, 'mate_id');
    // }

    // public function ratedMates()
    // {
    //     return $this->hasMany(MateRating::class, 'rated_mate_id');
    // }

    // public function spaceRatings()
    // {
    //     return $this->hasMany(SpaceRating::class);
    // }



    // // Relationship with ratings
    // public function ratings()
    // {
    //     return $this->hasMany(MatesRating::class, 'mate_id');
    // }

    // // Relationship with avatar (assuming you have a 'photo' column)
    // public function avatar()
    // {
    //     return $this->hasOne(User::class, 'id', 'rated_by_mate_id');
    // }
    
}
