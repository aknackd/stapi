<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\StarshipClass;

class StarshipClassesController extends Controller
{
    public function all(Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric|between:1,100',
        ]);

        $limit = $request->input('limit') ?: 15;

        return StarshipClass::paginate($limit);
    }

    public function show($starshipClass)
    {
        return is_numeric($starshipClass)
            ? StarshipClass::findOrFail($starshipClass)
            : StarshipClass::findByFieldOrFail('name', $starshipClass);
    }
}
