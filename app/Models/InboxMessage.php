<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxMessage extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'conversation_uid', 'sender_uid'];

    // Specify the table name
    protected $table = 'messages';

    public function conversation()
    {
        return $this->belongsTo(InboxConversation::class);
    }

    public function joinRequest()
    {
        return $this->hasOne(InboxJoinRequest::class, 'message_id', 'id');
    }
}
