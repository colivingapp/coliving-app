<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceMate extends Model
{
    use HasFactory;

    protected $table = 'space_mates';

    protected $fillable = [
        'space_uid',
        'mate_uid',
        'joined_at',
        'left_at',
        'status',
        'role',
    ];

    protected $dates = [
        'joined_at',
        'left_at',
    ];

}
