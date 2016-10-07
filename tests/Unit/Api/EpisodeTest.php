<?php

use App\Models\Episode;

class EpisodeTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that when we fetch all episodes that we get an array of episodes
     * in a paginated response.
     *
     * @test
     */
    public function shouldReturnArrayOfEpisodesPaginated()
    {
        $numEpisodes = 2;
        
        factory('App\Models\Episode', $numEpisodes)->create();

        $this->doGet('/api/v1/episodes');
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->assertEquals($numEpisodes, count($content['data']));
        $this->seeJson(['total' => $numEpisodes]);
        $this->seeJsonStructure([
            'total', 'per_page', 'current_page', 'last_page', 'next_page_url',
            'prev_page_url', 'from', 'to',
            'data' => [
                '*' => ['id', 'title'],
            ],
        ]);
    }

    /**
     * Test that we can retrieve an episode by its ID.
     *
     * @test
     */
    public function shouldReturnSingularEpisodeById()
    {
        $episode = factory('App\Models\Episode')->create();
        $id = $episode->id;

        $this->doGet('/api/v1/episodes/'.$id);

        $this->assertResponseOk();
        $this->seeJson(['title' => $episode->title]);
    }

    /**
     * Test that we get a 404 response when attempting to fetch an
     * episode that doesn't exist.
     *
     * @test
     */
    public function shouldReturn404EpisodeNotFound()
    {
        $this->doGet('/api/v1/episodes/i-do-not-exist');

        $this->assertResponseStatus(404);
    }

    /**
     * Test that we can fetch all episodes and get back a paginated response
     * when specifying a limit query parameter.
     *
     * @test
     */
    public function shouldReturnEpisodesPaginatedWithLimit()
    {
        $numEpisodes = 4;
        $limit = '2';

        factory('App\Models\Episode', $numEpisodes)->create();

        $this->doGet('/api/v1/episodes?limit='.$limit);

        $this->assertResponseOk();
        $this->seeJson(['total' => $numEpisodes, 'per_page' => $limit]);
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'title']],
        ]));
    }

    /**
     * Test that we get a 422 response when attempting to fetch all episodes
     * when specifying an invalid limit query parameter value.
     *
     * @test
     */
    public function shouldReturn422EpisodesWithInvalidLimit()
    {
        $episode = factory('App\Models\Episode')->create();

        $this->doGet('/api/v1/episodes?limit=-1');

        $this->assertResponseStatus(422);
        $this->seeJson(['limit' => ['The limit must be between 1 and 100.']]);
    }
}
