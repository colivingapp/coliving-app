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
