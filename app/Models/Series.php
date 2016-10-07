<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;

class Series extends Model
{
    use HasAppModelTrait;

    protected $fillable = [
        'name', 'abbreviation', 'studio', 'network', 'series_begin',
        'series_end', 'timeline_begin', 'timeline_end', 'num_seasons',
        'num_episodes',
    ];
}
