<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'track_id',
        'track_name',
        'artist_name',
        'album_name',
        'year',
        'popularity',
        'duration_ms',
        'language',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}