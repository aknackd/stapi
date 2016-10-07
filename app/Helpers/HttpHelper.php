<?php

namespace App\Helpers;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Request as GuzzleHttpRequest;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class HttpHelper
{
    /**
     * Build GuzzleHttp\Client options to pass along in every request.
     *
     * @param array $options Client options. These are merged with default options set in this method.
     * @return array GuzzleHttp\Client options.
     */
    protected static function clientOptions(array $options = [])
    {
        $defaultOptions = [];

        // Use vendored CA bundle on Windows since Windows doesn't include
        // cURL, OpenSSL, or a ca-certificates package like *nix
        if (EnvironmentHelper::isWindows()) {
            $defaultOptions['verify'] = realpath(app_path().'\\..\\ca-bundle.crt');
        }

        return array_merge($defaultOptions, $options);
    }

    /**
     * Fetch information about a remote file.
     *
     * @param string $url Remote URL
     * @return \GuzzleHttp\Psr7\Response Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If we don't get a 200 OK response when a HEAD request is made on $url
     */
    public static function getRemoteFileInfo($url)
    {
        $client = new GuzzleHttpClient();
        $response = $client->request('HEAD', $url, self::clientOptions());
        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new HttpException($response->getStatusCode(), 'Could not get remote file: '.$url);
        }

        return $response;
    }

    /**
     * Download a remote file.
     *
     * @param string $url Remote URL
     * @param string $outputFile Local output filename
     * @param \Symfony\Component\Console\Helper\ProgressBar Progress bar to display (only display if not `null`)
     */
    public static function downloadRemoteFile($url, $outputFile, ProgressBar $progressBar = null)
    {
        $options = self::clientOptions();
        if ($progressBar !== null) {
            $options['progress'] = function ($dlTotal, $dlSoFar, $upTotal, $upSoFar) use ($progressBar) {
                $progressBar->setProgress($dlSoFar / 1024);
            };
            $progressBar->setMessage('Downloading remote file');
            $progressBar->start();
        }

        $client = new GuzzleHttpClient($options);
        $request = new GuzzleHttpRequest('GET', $url);
        $client->send($request, ['sink' => $outputFile]);

        if ($progressBar !== null) {
            $progressBar->finish();
            $progressBar->clear();
        }
    }
}
