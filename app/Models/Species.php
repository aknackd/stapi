<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;

class Species extends Model
{
    use HasAppModelTrait;

    protected $fillable = ['name', 'type', 'quadrants', 'planets', 'population'];

    public function getQuadrantsAttribute($value)
    {
        return array_map(function ($item) {
            return trim(str_replace('quadrant', '', strtolower($item)));
        }, json_decode($value));
    }

    public function getPlanetsAttribute($value)
    {
        return json_decode($value);
    }
}
