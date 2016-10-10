<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Species;

class SpeciesController extends Controller
{
    public function all(Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric|between:1,100',
        ]);

        $limit = $request->input('limit') ?: 15;

        return Species::paginate($limit);
    }

    public function show(Species $species)
    {
        return $species;
    }
}
