<?php

class MissingApiRouteTest extends TestCase
{
    use MakesJsonRequests;

    /**
     * Test that we get a 404 response when attempting to request an
     * undefined route.
     *
     * @test
     */
    public function shouldReturn404ForUndefinedRoute()
    {
        $this->doGet('/api/v1/i/should/not/exist');

        $this->assertResponseStatus(404);
        $this->seeJson([
            'status' => 404,
            'error'  => 'Not Found',
        ]);
    }
}
