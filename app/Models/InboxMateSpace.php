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
        return $this->hasMany(Message::class);
    }

    public function mate()
    {
        return $this->belongsTo(Mate::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
