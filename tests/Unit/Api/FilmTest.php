<?php

use App\Models\Film;

class FilmTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that fetching all the films returns an array of films.
     *
     * @test
     */
    public function shouldReturnArrayOfFilms()
    {
        factory('App\Models\Film', 5)->create();

        $this->doGet('/api/v1/films');
        $content = json_decode($this->response->content());

        $this->assertResponseOk();
        $this->assertEquals(5, count($content));
        $this->seeJsonStructure([
            '*' => [
                'id', 'title', 'director', 'producers', 'length',
                'release_date', 'universe',
            ],
        ]);
    }

    /**
     * Test that we can retrieve a single film by its ID.
     *
     * @test
     */
    public function shouldReturnFilm()
    {
        $film = factory(Film::class)->create();
        $id = $film->id;

        $this->doGet('/api/v1/films/'.$id);

        $this->assertResponseOk();
        $this->seeJson(['id' => $id, 'title' => $film->title]);
    }

    /**
     * Test that we can retrieve all prime universe films.
     *
     * @test
     */
    public function shouldReturnPrimeUniverseFilms()
    {
        factory(Film::class, 20)->create();

        $this->doGet('/api/v1/films?universe=prime');
        $content = json_decode($this->response->content(), true);

        $primeUniverses = array_filter($content, function ($item) {
            return $item['universe'] == 'prime';
        });
        $nonPrimeUniverses = array_filter($content, function ($item) {
            return $item['universe'] != 'prime';
        });

        $this->assertResponseOk();
        $this->assertGreaterThan(0, count($content));
        $this->assertGreaterThan(0, count($primeUniverses));
        $this->assertEquals(0, count($nonPrimeUniverses));
        $this->seeJson(['universe' => 'prime']);
    }

    /**
     * Test that we can retrieve all Kelvin universe films.
     *
     * @test
     */
    public function shouldReturnKelvinUniverseFilms()
    {
        factory(Film::class, 20)->create();

        $this->doGet('/api/v1/films?universe=kelvin');
        $content = json_decode($this->response->content(), true);

        $kelvinUniverses = array_filter($content, function ($item) {
            return $item['universe'] == 'kelvin';
        });
        $nonKelvinUniverses = array_filter($content, function ($item) {
            return $item['universe'] != 'kelvin';
        });

        $this->assertResponseOk();
        $this->assertGreaterThan(0, count($content));
        $this->assertGreaterThan(0, count($kelvinUniverses));
        $this->assertEquals(0, count($nonKelvinUniverses));
        $this->seeJson(['universe' => 'kelvin']);
    }

    /**
     * Test that we get a 422 response when attempting to retrieve films
     * in an uknown univerise (prime or kelvin).
     *
     * @test
     */
    public function shouldReturn422InvalidUniverseParameter()
    {
        factory(Film::class, 3)->create();

        $this->doGet('/api/v1/films?universe=jjverse');

        $this->assertResponseStatus(422);
        $this->seeJson([
            'universe' => ['The selected universe is invalid.'],
        ]);
    }

    /**
     * Test that we get a 404 response when attempting to retrieve a
     * film by its ID when it doesn't exist.
     *
     * @test
     */
    public function shouldReturn404FilmNotFound()
    {
        $this->doGet('/api/v1/films/1337');

        $this->assertResponseStatus(404);
    }
}
