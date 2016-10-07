<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;

class Film extends Model
{
    use HasAppModelTrait;

    protected $fillable = ['title', 'director', 'producers', 'length', 'release_date', 'universe'];

    /**
     * Convert film length in seconds to hours/minutes formatted string.
     *
     * @param int $value Length in seconds
     * @return string Film length in `{0}h{1}s` format (ex: 90 -> 1h30m)
     */
    public function getLengthAttribute($value)
    {
        return gmdate('g\hi\m', $value*60);
    }

    public function getProducersAttribute($value)
    {
        return json_decode($value);
    }
}
