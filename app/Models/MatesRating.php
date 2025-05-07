<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatesRating extends Model
{
    use HasFactory;

    protected $table = 'mates_ratings';

    protected $fillable = ['user_id', 'from_user_id', 'rating', 'reference'];


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
