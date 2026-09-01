<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubPollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'hub_poll_id',
        'label',
        'position',
    ];

    public function poll()
    {
        return $this->belongsTo(HubPoll::class, 'hub_poll_id');
    }

    public function votes()
    {
        return $this->hasMany(HubPollVote::class);
    }
}
