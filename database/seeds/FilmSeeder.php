<?php

use Illuminate\Database\Seeder;
use App\Models\Film;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Film::insertMany([
            [
                'title'        => 'Star Trek: The Motion Picture',
                'director'     => 'Robert Wise',
                'producers'    => json_encode(['Gene Roddenberry']),
                'length'       => 145,
                'release_date' => '1979-12-07',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek II: The Wrath of Khan',
                'director'     => 'Nicholas Meyer',
                'producers'    => json_encode(['Robert Salin']),
                'length'       => 113,
                'release_date' => '1982-06-04',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek III: The Search for Spock',
                'director'     => 'Leonard Nimoy',
                'producers'    => json_encode(['Harve Bennet']),
                'length'       => 105,
                'release_date' => '1984-06-01',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek IV: The Voyage Home',
                'director'     => 'Leonard Nimoy',
                'producers'    => json_encode(['Harve Bennet']),
                'length'       => 122,
                'release_date' => '1986-11-19',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek V: The Final Frontier',
                'director'     => 'William Shatner',
                'producers'    => json_encode(['Harve Bennet']),
                'length'       => 107,
                'release_date' => '1989-06-09',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek VI: The Undiscovered Country',
                'director'     => 'Nicholas Meyer',
                'producers'    => json_encode(['Steven-Charles Jaffe', 'Ralph Winter']),
                'length'       => 113,
                'release_date' => '1991-12-06',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek Generations',
                'director'     => 'David Carson',
                'producers'    => json_encode(['Rick Berman']),
                'length'       => 118,
                'release_date' => '1994-11-18',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek: First Contact',
                'director'     => 'Jonathon Frakes',
                'producers'    => json_encode(['Rick Berman']),
                'length'       => 111,
                'release_date' => '1996-11-22',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek: Insurrection',
                'director'     => 'Jonathon Frakes',
                'producers'    => json_encode(['Rick Berman']),
                'length'       => 103,
                'release_date' => '1998-11-11',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek Nemesis',
                'director'     => 'Stuart Baird',
                'producers'    => json_encode(['Rick Berman']),
                'length'       => 116,
                'release_date' => '2002-12-12',
                'universe'     => 'prime',
            ],
            [
                'title'        => 'Star Trek',
                'director'     => 'J.J. Abrams',
                'producers'    => json_encode(['J.J. Abrams', 'Damon Lindelof']),
                'length'       => 128,
                'release_date' => '2009-05-07',
                'universe'     => 'kelvin',
            ],
            [
                'title'        => 'Star Trek Into Darkness',
                'director'     => 'J.J. Abrams',
                'producers'    => json_encode(['J.J. Abrams', 'Bryan Burk', 'Alex Kurtzman', 'Roberto Orci']),
                'length'       => 143,
                'release_date' => '2013-05-16',
                'universe'     => 'kelvin',
            ],
            [
                'title'        => 'Star Trek Beyond',
                'director'     => 'Justin Lin',
                'producers'    => json_encode(['J.J. Abrams', 'Bryan Burk', 'Jeffrey Chernov', 'David Ellison', 'Roberto Orci']),
                'length'       => 122,
                'release_date' => '2016-07-22',
                'universe'     => 'kelvin',
            ],
        ]);
    }
}
