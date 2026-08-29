<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_uid',
        'author_user_id',
        'kind',
        'title',
        'description',
        'start_date',
        'end_date',
        'starts_at',
        'ends_at',
        'location',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
