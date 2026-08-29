<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinRequestVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'join_request_id',
        'user_id',
        'vote',
    ];
}
