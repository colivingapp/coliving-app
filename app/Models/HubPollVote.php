<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubPollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'hub_poll_id',
        'hub_poll_option_id',
        'user_id',
    ];

    public function option()
    {
        return $this->belongsTo(HubPollOption::class, 'hub_poll_option_id');
    }
}
