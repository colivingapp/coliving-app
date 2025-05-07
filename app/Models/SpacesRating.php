<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpacesRating extends Model
{
    use HasFactory;

    protected $table = 'spaces_ratings';

    protected $fillable = ['space_uid', 'from_user_id', 'rating', 'reference'];


    public function ratedByUser()
    {
        return $this->belongsTo('App\Models\Mate', 'from_user_id', 'user_id');
    }

    // public function ratedByUser()
    // {
    //     return $this->belongsTo('App\Models\Mate', 'from_user_id', 'user_id')
    //         ->select('uid', 'username', 'name', 'profile_pic', 'photo', 'avatar');
    // }

}
