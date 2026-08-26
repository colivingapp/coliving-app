<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxMateSpace extends Model
{
    use HasFactory;

    // Define the fillable properties
    protected $fillable = ['uid', 'user1', 'user2'];

    // Specify the table name
    protected $table = 'inbox_mate_space';

    // Define the relationships
    public function messages()
    {
        return $this->hasMany(InboxMessage::class, 'conversation_uid', 'uid');
    }

    public function mate()
    {
        return $this->belongsTo(Mate::class, 'user1', 'uid');
    }

    public function space()
    {
        return $this->belongsTo(Space::class, 'user2', 'uid');
    }
}
