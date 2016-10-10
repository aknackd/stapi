<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define('App\Models\User', function (Faker\Generator $faker) {
    static $password;

    return [
        'name'           => $faker->name,
        'email'          => $faker->unique()->safeEmail,
        'password'       => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define('App\Models\Series', function (Faker\Generator $faker) {
    return [
        'name'           => $faker->text,
        'abbreviation'   => strtoupper(substr($faker->word, 0, 3)),
        'studio'         => $faker->word,
        'network'        => $faker->word,
        'series_begin'   => $faker->date,
        'series_end'     => $faker->date,
        'timeline_begin' => $faker->dateTimeBetween('2151-01-01', '2378-12-31')->format('Y'),
        'timeline_end'   => $faker->dateTimeBetween('2151-01-01', '2378-12-31')->format('Y'),
        'num_seasons'    => $faker->numberBetween(1, 7),
        'num_episodes'   => $faker->numberBetween(13, 30),
    ];
});

$factory->define('App\Models\Film', function (Faker\Generator $faker) {
    $producers = array_fill(0, $faker->numberBetween(1, 8), null);
    $producers = array_map(function ($item) use ($faker) {
        return $faker->name();
    }, $producers);

    return [
        'title'        => $faker->text,
        'director'     => $faker->name,
        'producers'    => json_encode($producers),
        'length'       => $faker->numberBetween(60, 180),
        'release_date' => $faker->date,
        'universe'     => $faker->randomElement(['prime', 'kelvin']),
    ];
});

$factory->define('App\Models\Episode', function (Faker\Generator $faker) {
    $series = factory('App\Models\Series')->create();

    return [
        'title'         => $faker->text,
        'series_id'     => $series->id,
        'season_num'    => $faker->numberBetween(1, $series->num_seasons),
        'episode_num'   => $faker->numberBetween(1, 30),
        'serial_number' => $faker->regexify('[0-9]{4}-[0-9]{2}'),
        'air_date'      => $faker->date,
    ];
});

$factory->define('App\Models\Species', function (Faker\Generator $faker) {
    $numQuadrants = $faker->numberBetween(0, 4);
    $numPlanets = $faker->numberBetween(0, 3);
    $quadrants = $faker->randomElements(['Alpha', 'Beta', 'Gamma', 'Delta'], $numQuadrants);
    
    return [
        'name'       => $faker->word,
        'type'       => $faker->word,
        'quadrants'  => json_encode(array_flatten([$quadrants])),
        'planets'    => json_encode($faker->words($numPlanets)),
        'population' => (string) $faker->randomNumber,
    ];
});
