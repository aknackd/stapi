<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Series;

class SeriesController extends Controller
{
    public function all(Request $request)
    {
        return Series::all();
    }

    public function show($series)
    {
        return is_numeric($series)
            ? Series::findOrFail($series)
            : Series::findByFieldOrFail('abbreviation', strtoupper($series))->first();
    }

    public function episodes(Request $request, $series)
    {
        $series = is_numeric($series)
            ? Series::findOrFail($series)
            : Series::findByFieldOrFail('abbreviation', strtoupper($series))->first();

        $this->validate($request, [
            'limit'  => 'numeric|between:1,100',
            'season' => 'numeric|between:1,'.$series->num_seasons,
        ]);

        $limit = $request->input('limit') ?: 15;

        $query = Episode::forSeries($series->id);

        // fetch episodes for a particular season
        if ($request->has('season')) {
            $query->where('season_num', $request->input('season'));
        }

        return $query
            ->orderBy('season_num', 'asc')
            ->orderby('episode_num', 'asc')
            ->paginate($limit);
    }
}
