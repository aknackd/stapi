<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function () {
    Route::get('series',                   'SeriesController@all');
    Route::get('series/{series}',          'SeriesController@show');
    Route::get('series/{series}/episodes', 'SeriesController@episodes');
    Route::get('episodes',                 'EpisodesController@all');
    Route::get('episodes/{episode}',       'EpisodesController@show');
    Route::get('films',                    'FilmsController@all');
    Route::get('films/{film}',             'FilmsController@show');
    Route::get('/species',                 'SpeciesController@all');
    Route::get('/species/{species}',       'SpeciesController@show');
    Route::get('/starships',               'StarshipsController@all');
    Route::get('/starships/{starship}',    'StarshipsController@show');
});
