<?php

trait MakesJsonRequests
{
    /**
     * Laravel's paginated response JSON structure.
     *
     * @var array
     */
    protected static $paginatedResponseStructure = [
        'total', 'per_page', 'current_page', 'last_page', 'next_page_url',
        'prev_page_url', 'from', 'to', 'data',
    ];

    /**
     * Add JSON content headers to request.
     *
     * @param array $headers Headers
     */
    private function addJsonContentHeaders(array &$headers)
    {
        $headers = array_merge($headers, [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Perform a GET request on a URI.
     *
     * @param  string $uri     URI
     * @param  array  $headers Headers
     * @return $this
     */
    public function doGet($uri, array $headers = [])
    {
        $this->addJsonContentHeaders($headers);

        return $this->get($uri, $headers);
    }

    /**
     * Performs a POST request on a URI.
     *
     * @param  string $uri     URI
     * @param  array  $data    Data
     * @param  array  $headers Headers
     * @return $this
     */
    public function doPost($uri, array $data = [], array $headers = [])
    {
        $this->addJsonContentHeaders($headers);

        return $this->post($uri, $data, $headers);
    }

    /**
     * Performs a PUT request on a URI.
     *
     * @param  string $uri     URI
     * @param  array  $data    Data
     * @param  array  $headers Headers
     * @return $this
     */
    public function doPut($uri, array $data = [], array $headers = [])
    {
        $this->addJsonContentHeaders($headers);

        return $this->put($uri, $data, $headers);
    }

    /**
     * Performs a PATCH request on a URI.
     *
     * @param  string $uri     URI
     * @param  array  $data    Data
     * @param  array  $headers Headers
     * @return $this
     */
    public function doPatch($uri, array $data = [], array $headers = [])
    {
        $this->addJsonContentHeaders($headers);

        return $this->patch($uri, $data, $headers);
    }

    /**
     * Performs a DELETE request on a URI.
     *
     * @param  string $uri     URI
     * @param  array  $data    Data
     * @param  array  $headers Headers
     * @return $this
     */
    public function doDelete($uri, array $data = [], array $headers = [])
    {
        $this->addJsonContentHeaders($headers);

        return $this->delete($uri, $data, $headers);
    }
}
