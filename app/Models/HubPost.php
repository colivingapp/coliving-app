<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'hub_thread_id',
        'space_uid',
        'author_user_id',
        'author_uid',
        'content',
    ];

    public function thread()
    {
        return $this->belongsTo(HubThread::class, 'hub_thread_id');
    }
}
