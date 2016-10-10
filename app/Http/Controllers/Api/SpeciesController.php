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
            'type'  => 'string',
        ]);

        $limit = $request->input('limit') ?: 15;

        $query = Species::query();

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        return $query->paginate($limit);
    }

    public function show(Species $species)
    {
        return $species;
    }
}
