<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAppModelTrait;

class Episode extends Model
{
    use HasAppModelTrait;

    protected $fillable = [
        'title', 'series_id', 'season_num', 'episode_num',
        'serial_number', 'air_date'
    ];

    public function series()
    {
        return $this->belongsTo('App\Models\Series');
    }

    public function scopeForSeries(Builder $query, $seriesId)
    {
        return $query->where('series_id', $seriesId)
            ->orderBy('season_num', 'asc')
            ->orderBy('episode_num', 'asc');
    }

    public static function findBySeasonAndEpisodeOrFail($value)
    {
        $item = collect();

        // search by season and episode number (i.e. s5e10)

        if (preg_match('/s(?P<season>\d)e(?P<episode>\d+)/', $value, $matches)) {
            $item = Episode::query()
                ->where('season_num',  (int) $matches['season'])
                ->where('episode_num', (int) $matches['episode']);
        }

        if ($item->count() != 1) {
            (new self)->throwNotFoundException();
        }

        return $item->first();
    }
}
