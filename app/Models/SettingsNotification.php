<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'new_conversation_started',
        'new_join_request_received',
        'new_review_received',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
