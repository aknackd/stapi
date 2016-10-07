<?php

use Illuminate\Database\Seeder;

use App\Models\Series;

class SeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Series::insertMany([
            [
                'name'           => 'Star Trek: The Original Series',
                'abbreviation'   => 'TOS',
                'studio'         => 'Desilu',
                'network'        => 'NBC',
                'series_begin'   => '1966-09-08',
                'series_end'     => '1969-06-03',
                'timeline_begin' => '2265',
                'timeline_end'   => '2269',
                'num_seasons'    => 3,
                'num_episodes'   => 79,
            ],
            [
                'name'           => 'Star Trek: The Animated Series',
                'abbreviation'   => 'TAS',
                'studio'         => 'Filmation',
                'network'        => 'NBC',
                'series_begin'   => '1973-09-08',
                'series_end'     => '1974-10-12',
                'timeline_begin' => '2269',
                'timeline_end'   => '2270',
                'num_seasons'    => 2,
                'num_episodes'   => 22,
            ],
            [
                'name'           => 'Star Trek: The Next Generation',
                'abbreviation'   => 'TNG',
                'studio'         => 'Paramount Pictures',
                'network'        => 'Syndicated (by Viacom)',
                'series_begin'   => '1987-09-28',
                'series_end'     => '1994-05-23',
                'timeline_begin' => '2364',
                'timeline_end'   => '2370',
                'num_seasons'    => 7,
                'num_episodes'   => 176,
            ],
            [
                'name'           => 'Star Trek: Deep Space Nine',
                'abbreviation'   => 'DS9',
                'studio'         => 'Paramount Pictures',
                'network'        => 'Syndicated (by Viacom)',
                'series_begin'   => '1993-01-03',
                'series_end'     => '1999-06-02',
                'timeline_begin' => '2369',
                'timeline_end'   => '2375',
                'num_seasons'    => 7,
                'num_episodes'   => 173,
            ],
            [
                'name'           => 'Star Trek: Voyager',
                'abbreviation'   => 'VOY',
                'studio'         => 'Paramount Pictures',
                'network'        => 'UPN',
                'series_begin'   => '1995-01-16',
                'series_end'     => '2001-05-23',
                'timeline_begin' => '2371',
                'timeline_end'   => '2378',
                'num_seasons'    => 7,
                'num_episodes'   => 168,
            ],
            [
                'name'           => 'Star Trek: Enterprise',
                'abbreviation'   => 'ENT',
                'studio'         => 'Paramount Pictures',
                'network'        => 'UPN',
                'series_begin'   => '2001-09-26',
                'series_end'     => '2005-05-13',
                'timeline_begin' => '2151',
                'timeline_end'   => '2155',
                'num_seasons'    => 4,
                'num_episodes'   => 97,
            ],
            [
                'name'           => 'Star Trek Discovery',
                'abbreviation'   => 'DIS',
                'studio'         => 'CBS Television Studios',
                'network'        => 'CBS All Access',
                'series_begin'   => null,
                'series_end'     => null,
                'timeline_begin' => 2255,
                'timeline_end'   => null,
                'num_seasons'    => null,
                'num_episodes'   => null,
            ]
        ]);
    }
}
