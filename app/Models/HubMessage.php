<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_uid',
        'sender_id',
        'sender_uid',
        'content',
    ];
}
