<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Starship;

class StarshipsController extends Controller
{
    public function all(Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric|between:1,100',
        ]);

        $limit = $request->input('limit') ?: 15;

        return Starship::paginate($limit);
    }

    public function show($starship)
    {
        return is_numeric($starship)
            ? Starship::findOrFail($starship)
            : Starship::findByFieldOrFail('registry_number', $starship);
    }
}
