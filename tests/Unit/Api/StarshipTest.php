<?php

use App\Models\Starship;

class StarshipTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that fetching all species returns an array of starships
     * in a paginated response.
     *
     * @test
     */
    public function shouldReturnArrayOfStarshipsPaginated()
    {
        $numShips = 2;
        factory('App\Models\Starship', $numShips)->create();

        $this->doGet('/api/v1/starships');
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->assertEquals($numShips, count($content['data']));
        $this->seeJson(['total' => $numShips]);
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'name', 'registry_number']]
        ]));
    }

    /**
     * Test that we can retrieve a single starship by its ID.
     *
     * @test
     */
    public function shouldReturnSingularStarshipById()
    {
        $starship = factory('App\Models\Starship')->create();
        $id = $starship->id;

        $this->doGet('/api/v1/starships/'.$id);

        $this->assertResponseOk();
        $this->seeJson(['name' => $starship->name]);
        $this->seeJsonStructure(['id', 'name', 'registry_number']);
    }

    /**
     * Test that we can retrieve a single starship by its registry number.
     *
     * @test
     */
    public function shouldReturnSingularStarshipByRegistryNumber()
    {
        $starship = factory('App\Models\Starship')->create();

        $this->doGet('/api/v1/starships/'.$starship->registry_number);

        $this->assertResponseOk();
        $this->seeJson(['name' => $starship->name, 'registry_number' => $starship->registry_number]);
        $this->seeJsonStructure(['*' => ['id', 'name', 'registry_number']]);
    }

    /**
     * Test that we get a 404 when we attempt to retrieve
     * a starship that doesn't exist.
     *
     * @test
     */
    public function shouldReturn404StarshipNotFound()
    {
        $this->doGet('/api/v1/starships/99999999');

        $this->assertResponseStatus(404);
    }

    /**
     * Test that when we retrieve starships we can limit the
     * number of results that are returned.
     *
     * @test
     */
    public function shouldReturnStarshipsPaginatedWithLimit()
    {
        $numShips = 5;
        $limit = '2';

        $species = factory('App\Models\Starship', $numShips)->create();

        $this->doGet('/api/v1/starships?limit='.$limit);
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'name', 'registry_number']]
        ]));
        $this->seeJson(['per_page' => $limit]);
    }

    /**
     * Test that we will get a 422 when attempting to fetch starships
     * when given an invalid number of results per page.
     *
     * @test
     */
    public function shouldReturn422StarshipsWithInvalidLimit()
    {
        $species = factory('App\Models\Starship')->create();

        $this->doGet('/api/v1/starships?limit=-1');

        $this->assertResponseStatus(422);
        $this->seeJson(['limit' => ['The limit must be between 1 and 100.']]);
    }
}
