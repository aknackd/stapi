<?php

use App\Models\StarshipClass;

class StarshipClassTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that fetching all starship classes returns an array of starship
     * classes in a paginated response.
     *
     * @test
     */
    public function shouldReturnArrayOfStarshipClassesPaginated()
    {
        $numClasses = 2;
        factory('App\Models\StarshipClass', $numClasses)->create();

        $this->doGet('/api/v1/starship_classes');
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->assertEquals($numClasses, count($content['data']));
        $this->seeJson(['total' => $numClasses]);
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'name', 'owner', 'operator']]
        ]));
    }

    /**
     * Test that we can retrieve a single starship class by its ID.
     *
     * @test
     */
    public function shouldReturnSingularStarshipClassById()
    {
        $starshipClass = factory('App\Models\StarshipClass')->create();
        $id = $starshipClass->id;

        $this->doGet('/api/v1/starship_classes/'.$id);

        $this->assertResponseOk();
        $this->seeJson(['name' => $starshipClass->name]);
        $this->seeJsonStructure(['id', 'name', 'owner', 'operator']);
    }

    /**
     * Test that we get a 404 when we attempt to retrieve
     * a starship class that doesn't exist.
     *
     * @test
     */
    public function shouldReturn404StarshipClassNotFound()
    {
        $this->doGet('/api/v1/starship_classes/99999999');

        $this->assertResponseStatus(404);
    }

    /**
     * Test that when we retrieve starships classes we can limit the
     * number of results that are returned.
     *
     * @test
     */
    public function shouldReturnStarshipClassesPaginatedWithLimit()
    {
        $numClasses = 5;
        $limit = '2';

        $starshipClasses = factory('App\Models\StarshipClass', $numClasses)->create();

        $this->doGet('/api/v1/starship_classes?limit='.$limit);
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'name', 'owner', 'operator']]
        ]));
        $this->seeJson(['per_page' => $limit]);
    }

    /**
     * Test that we will get a 422 when attempting to fetch starship classes
     * when given an invalid number of results per page.
     *
     * @test
     */
    public function shouldReturn422StarshipClassesWithInvalidLimit()
    {
        $starshipClass = factory('App\Models\StarshipClass')->create();

        $this->doGet('/api/v1/starship_classes?limit=-1');

        $this->assertResponseStatus(422);
        $this->seeJson(['limit' => ['The limit must be between 1 and 100.']]);
    }
}
