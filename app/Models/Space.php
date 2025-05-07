<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Space extends Model
{
    use HasFactory;

    protected $table = 'spaces';

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function ratings()
    {
        return $this->hasMany(SpaceRating::class);
    }
}
