<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubPoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'hub_thread_id',
        'space_uid',
        'author_user_id',
        'question',
        'allow_multiple',
    ];

    protected $casts = [
        'allow_multiple' => 'boolean',
    ];

    public function thread()
    {
        return $this->belongsTo(HubThread::class, 'hub_thread_id');
    }

    public function options()
    {
        return $this->hasMany(HubPollOption::class)->orderBy('position');
    }
}
