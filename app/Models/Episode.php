<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Import an episode from a Memory Alpha database dump record. Used in the
     * `stapi:import` artisan command.
     *
     * @param array $data Data
     * @return \App\Models\Episode Created episode instance
     */
    public static function import(array $data = [])
    {
        // NOTE: Multi-part episodes may not have an integer for its
        //       episode number. For example, VOY: Caretaker (s1e01) is
        //       a two-parter but only has one entry with
        //       `nEpisode = 01/02`; the next episode VOY: Parallax is
        //       `nEpisode = 3` (s1e03).

        // Some serial dates aren't complete so piece it together by its parts
        if (isset($data['nSerialAirdate']) && strlen($data['nSerialAirdate']) != 10) {
            foreach (['Release', 'Airdate'] as $type) {
                if (!isset($data["n{$type}Year"])) {
                    continue;
                }

                $dt = new Carbon(sprintf('%s %s %s',
                    $data["s{$type}Month"],
                    $data["s{$type}Day"],
                    $data["s{$type}Year"]));
                $data['nSerialAirdate'] = $dt->toDateString();
                unset($dt);
            }
        }

        return self::create([
            'title'         => $data['title'],
            'series_id'     => $data['series_id'],
            'season_num'    => (int) $data['nSeason'],
            'episode_num'   => (int) $data['nEpisode'],
            'serial_number' => $data['sProductionSerialNumber'],
            'air_date'      => $data['nSerialAirdate'],
        ]);
    }
}
