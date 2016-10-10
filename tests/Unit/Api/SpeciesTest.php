<?php

use App\Models\Species;

class SpeciesTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that fetching all species returns an array of species
     * in a paginated response.
     *
     * @test
     */
    public function shouldReturnArrayOfSpeciesPaginated()
    {
        $numSpecies = 2;
        factory('App\Models\Species', $numSpecies)->create();

        $this->doGet('/api/v1/species');
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->assertEquals($numSpecies, count($content['data']));
        $this->seeJson(['total' => $numSpecies]);
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'name']]
        ]));
    }

    /**
     * Test that we can retrieve a single species by its ID.
     *
     * @test
     */
    public function shouldReturnSingularSpeciesById()
    {
        $species = factory('App\Models\Species')->create();
        $id = $species->id;

        $this->doGet('/api/v1/species/'.$id);

        $this->assertResponseOk();
        $this->seeJson(['name' => $species->name]);
    }

    /**
     * Test that we get a 404 when we attempt to retrieve
     * a species that doesn't exist.
     *
     * @test
     */
    public function shouldReturn404SpeciesNotFound()
    {
        $this->doGet('/api/v1/species/1337');

        $this->assertResponseStatus(404);
    }

    /**
     * Test that when we retrieve the episodes for a series
     * we can limit the number of results that are returned.
     *
     * @test
     */
    public function shouldReturnSpeciesPaginatedWithLimit()
    {
        $numSpecies = 5;
        $limit = '2';

        $species = factory('App\Models\Species', $numSpecies)->create();

        $this->doGet('/api/v1/species?limit='.$limit);
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'name']]
        ]));
        $this->seeJson(['per_page' => $limit]);
    }

    /**
     * Test that we will get a 422 when attempting to fetch species
     * when given an invalid number of results per page.
     *
     * @test
     */
    public function shouldReturn422SpeciesWithInvalidLimit()
    {
        $species = factory('App\Models\Species')->create();

        $this->doGet('/api/v1/species?limit=-1');

        $this->assertResponseStatus(422);
        $this->seeJson(['limit' => ['The limit must be between 1 and 100.']]);
    }
}
