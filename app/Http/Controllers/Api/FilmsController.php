<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Film;

class FilmsController extends Controller
{
    public function all(Request $request)
    {
        $this->validate($request, [
            'universe' => 'string|in:prime,kelvin',
        ]);

        $query = Film::query();

        if ($request->has('universe')) {
            $query->where('universe', $request->input('universe'));
        }

        return $query->orderBy('release_date', 'asc')->get();
    }

    public function show(Film $film)
    {
        return $film;
    }
}
