<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxMateMate extends Model
{
    use HasFactory;

    // Define the fillable properties
    protected $fillable = ['uid', 'user1', 'user2'];
    
    // Specify the table name
    protected $table = 'inbox_mate_mate';

    // Define the relationships
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->hasMany(Mate::class);
    }

}