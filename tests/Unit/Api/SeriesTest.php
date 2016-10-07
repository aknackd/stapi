<?php

use App\Models\Series;

class SeriesTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that fetching all series returns an array of series.
     *
     * @test
     */
    public function shouldReturnArrayOfSeries()
    {
        $numSeries = 2;
        factory('App\Models\Series', $numSeries)->create();

        $this->doGet('/api/v1/series');
        $content = json_decode($this->response->content());

        $this->assertResponseOk();
        $this->assertEquals($numSeries, count($content));
    }

    /**
     * Test that we can retrieve a single series by its ID.
     *
     * @test
     */
    public function shouldReturnSingularSeriesById()
    {
        $series = factory('App\Models\Series')->create();
        $id = $series->id;

        $this->doGet('/api/v1/series/'.$id);

        $this->assertResponseOk();
        $this->seeJson(['name' => $series->name]);
    }

    /**
     * Test that we can retrieve a single series by
     * its abbreviation (lowercase).
     *
     * @test
     */
    public function shouldReturnSingularSeriesByAbbreviationLowerCase()
    {
        $series = factory('App\Models\Series')->create();
        $abbrev = $series->abbreviation;

        $this->doGet('/api/v1/series/'.strtolower($abbrev));

        $this->assertResponseOk();
        $this->seeJson(['abbreviation' => $abbrev]);
    }

    /**
     * Test that we can retrieve a single series by
     * its abbreviation (uppercase).
     *
     * @test
     */
    public function shouldReturnSingularSeriesByAbbreviationUpperCase()
    {
        $series = factory('App\Models\Series')->create();
        $abbrev = $series->abbreviation;

        $this->doGet('/api/v1/series/'.strtoupper($abbrev));

        $this->assertResponseOk();
        $this->seeJson(['abbreviation' => $abbrev]);
    }

    /**
     * Test that we can retrieve a single series by
     * its abbreviation (mixed case).
     *
     * @test
     */
    public function shouldReturnSingularSeriesByAbbreviationMixedCase()
    {
        $series = factory('App\Models\Series')->create();
        $abbrev = $series->abbreviation;

        $mixedCaseAbbrev = '';
        for ($idx = 0; $idx < strlen($abbrev); $idx++) {
            $mixedCaseAbbrev .= rand(0, 100) > 50
                ? strtoupper($abbrev[$idx])
                : strtolower($abbrev[$idx]);
        }

        $this->doGet('/api/v1/series/'.$mixedCaseAbbrev);

        $this->assertResponseOk();
        $this->seeJson(['abbreviation' => $abbrev]);
    }

    /**
     * Test that we get a 404 when we attempt to retrieve
     * a series that doesn't exist.
     *
     * @test
     */
    public function shouldReturn404SeriesNotFound()
    {
        $this->doGet('/api/v1/series/i-do-not-exist');

        $this->assertResponseStatus(404);
    }

    /**
     * Test that we can retrieve episodes for a series given
     * the series ID.
     *
     * @test
     */
    public function shouldReturnSeriesEpisodesById()
    {
        $episode = factory('App\Models\Episode')->create();
        $seriesId = $episode->series_id;

        $this->doGet('/api/v1/series/'.$seriesId.'/episodes');

        $this->assertResponseOk();
        $this->seeJson(['id' => $episode->id, 'title' => $episode->title]);
    }

    /**
     * Test that when we retrieve the episodes for a series by abbreviation
     * that the response is paginated.
     *
     * @test
     */
    public function shouldReturnSeriesEpisodesByAbbreviationPaginated()
    {
        $episodes = factory('App\Models\Episode', 5)->create();
        $abbrev = $episodes->first()->series->abbreviation;

        $this->doGet('/api/v1/series/'.$abbrev.'/episodes');

        $this->assertResponseOk();
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'title']]
        ]));
    }

    /**
     * Test that when we retrieve the episodes for a series by the series
     * ID and by season that the response will be paginated.
     *
     * @test
     */
    public function shouldReturnSeriesEpisodesBySeasonBySeriesIdPaginated()
    {
        $episodes = factory('App\Models\Episode', 5)->create();
        $seriesId = $episodes->first()->series->id;
        $seasonNum = $episodes->first()->season_num;

        $this->doGet('/api/v1/series/'.$seriesId.'/episodes?season='.$seasonNum);
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'title']]
        ]));
    }

    /**
     * Test that when we retrieve the episodes for a series by abbreviation
     * we can limit the number of results that are returned.
     *
     * @test
     */
    public function shouldReturnSeriesEpisodesPaginatedWithLimit()
    {
        $numEpisodes = 5;
        $limit = '2';

        $episodes = factory('App\Models\Episode', $numEpisodes)->create();
        $seriesId = $episodes->first()->series->id;

        $this->doGet('/api/v1/series/'.$seriesId.'/episodes?limit='.$limit);
        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();
        $this->seeJsonStructure(array_merge(self::$paginatedResponseStructure, [
            'data' => ['*' => ['id', 'title']]
        ]));
        $this->seeJson(['per_page' => $limit]);
    }

    /**
     * Test that we will get a 422 when attempting to fetch episodes
     * for a series when given an invalid number of results per page.
     *
     * @test
     */
    public function shouldReturn422SeriesEpisodesWithInvalidLimit()
    {
        $episode = factory('App\Models\Episode')->create();
        $seriesId = $episode->series->id;

        $this->doGet('/api/v1/series/'.$seriesId.'/episodes?limit=-1');

        $this->assertResponseStatus(422);
        $this->seeJson(['limit' => ['The limit must be between 1 and 100.']]);
    }

    /**
     * Test that we will get a 422 when attempting to fetch episodes
     * for a series when given an invalid season number.
     *
     * @test
     */
    public function shouldReturn422SeriesEpisodesWithInvalidSeason()
    {
        $episode = factory('App\Models\Episode')->create();
        $seriesId = $episode->series->id;
        $numSeasons = $episode->series->num_seasons;

        $this->doGet('/api/v1/series/'.$seriesId.'/episodes?season=-1');

        $this->assertResponseStatus(422);
        $this->seeJson([
            'season' => ['The season must be between 1 and '.$numSeasons.'.'],
        ]);
    }
}
