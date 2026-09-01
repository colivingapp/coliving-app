<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = ['space_uid', 'space_id', 'filename', 'pos'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}