<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_uid',
        'author_user_id',
        'title',
        'type',
        'is_sticky',
        'is_locked',
        'last_post_at',
    ];

    protected $casts = [
        'is_sticky' => 'boolean',
        'is_locked' => 'boolean',
        'last_post_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(HubPost::class);
    }
}
