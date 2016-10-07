<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Episode;

class EpisodesController extends Controller
{
    public function all(Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric|between:1,100',
        ]);

        $limit = $request->input('limit') ?: 15;

        return Episode::query()
            ->orderBy('season_num', 'asc')
            ->orderBy('episode_num', 'asc')
            ->paginate($limit);
    }

    public function show($episode)
    {
        if (is_numeric($episode)) {
            return Episode::findOrFail($episode);
        }

        return Episode::findBySeasonAndEpisodeOrFail($episode);
    }
}
