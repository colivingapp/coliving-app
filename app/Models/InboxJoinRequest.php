<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxJoinRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'mate_id',
        'mate_uid',
        'space_uid',
        'message_id',
        'status',
        'reminder_sent',
    ];
    
    // Specify the table name
    protected $table = 'join_requests';
}
