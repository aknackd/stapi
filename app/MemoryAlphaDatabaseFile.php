<?php

namespace App;

use Exception;
use Symfony\Console\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Helper\ProgressBar;
use Carbon\Carbon;
use Archive7z\Archive7z;
use Prewk\XmlStringStreamer;
use App\Helpers\EnvironmentHelper;
use App\Helpers\HttpHelper;
use App\Models\Episode;
use App\Models\Series;

final class MemoryAlphaDatabaseFile
{
    /**
     * Location of the current database dump from Memory Alpha.
     *
     * @var string
     */
    const DATABASE_DUMP_URL = 'https://s3.amazonaws.com/wikia_xml_dumps/e/en/enmemoryalpha_pages_current.xml.7z';

    /**
     * Retrieve the full path to the Memory Alpha database dump file.
     *
     * @param string $basedir Base directory
     * @return string Filename path
     */
    public static function localFile($basedir)
    {
        return $basedir.'/'.basename(parse_url(self::DATABASE_DUMP_URL, PHP_URL_PATH));
    }

    /**
     * Retrieve information about the remote Memory Alpha database dump file.
     *
     * @return \GuzzleHttp\Psr7\Response Response
     */
    public static function info()
    {
        return HttpHelper::getRemoteFileInfo(self::DATABASE_DUMP_URL);
    }

    /**
     * Retrieve the filesize of the remote Memory Alpha database dump file.
     * Used in displaying a progress bar while downloading the file (if
     * configured to do so).
     *
     * @return int Filesize in bytes
     * @throws \Exception If filesize cannot be determined due to a missing
     *                    `Content-Length` header in the response.
     */
    public static function size()
    {
        $response = HttpHelper::getRemoteFileInfo(self::DATABASE_DUMP_URL);

        if (!$response->hasHeader('Content-Length')) {
            throw new Exception(
                'Unable to retrieve Memory Alpha database dump size: Missing Content-Type header');
        }
            
        return $response->getHeaderLine('Content-Length');
    }

    /**
     * Downloads the remote Memory Alpha database dump to local disk. Can
     * optionally display a progress bar to inform the user of the download
     * status.
     *
     * @param string $downloadDir Directory to store downloaded file
     * @param \Symfony\Component\Console\Helper\ProgressBar Progress bar instance to use. If `null` then no progress bar is displayed.
     * @return string Local filename path of the downloaded file
     * @throws \Exception If unable to find the filesize of the remote database dump file
     * @throws \Exception If unable to determine the content type of the remote database dump file used to validate the file
     * @throws \Exception If the remote database dump file is empty
     * @throws \Exception If the remote database dump file is an expected 7-zip archive
     */
    public static function download($downloadDir, ProgressBar $progressBar = null)
    {
        $url = self::DATABASE_DUMP_URL;
        $localFile = $downloadDir.'/'.basename(parse_url($url, PHP_URL_PATH));
        $errorPrefix = 'Could not download remote file: ';

        $info = $this->info();

        if (!$info->hasHeader('Content-Length')) {
            throw new Exception(
                $errorPrefix.'Could not find Content-Length header in response to determine filesize');
        }
        if (!$info->hasHeader('Content-Type')) {
            throw new Exception(
                $errorPrefix.'Could not find Content-Type header in response to validate remote file');
        }

        if ($info->getHeaderLine('Content-Length') == 0) {
            throw new Exception(
                $errorPrefix.'Expected a non-empty file');
        }
        if ($info->getHeaderLine('Content-Type') != 'application/x-7z-compressed') {
            throw new Exception(
                $errorPrefix.'Unexpected content type detected, expected application/x-7z-compressed but got '.$contentType);
        }

        HttpHelper::downloadRemoteFile($url, $localFile, $progressBar);

        return $localFile;
    }

    /**
     * Extract the Memory Alpha database dump 7-zip archive into an output
     * directory.
     *
     * @param string $filename  Local filename path
     * @param string $outputDir Local directory to store extracted archive contents
     * @throws \Exception If unable to extract the Memory Alpha database dump file successfully
     */ 
    public static function extract($filename, $outputDir)
    {
        $importFile = null;
        
        // Extract local file
        $archive = new Archive7z($filename, EnvironmentHelper::findInPath('7za'));
        foreach ($archive->getEntries() as $entry) {
            if ($entry->getPath() == basename($filename, '.7z')) {
                $entry->extractTo($outputDir);
                $importFile = $outputDir.'/'.$entry->getPath();
                break;
            }
        }
        unset($archive);

        // Verify file was extracted properly
        if ($importFile === null || !file_exists($importFile) || !is_readable($importFile)) {
            throw new Exception(
                'Failed to extract Memory Alpha database dump: '.$filename);
        }

        return $importFile;
    }

    /**
     * Validate that a local Memory Alpha database dump file is a valid file.
     * This is a simple check that just verifies it is a MediaWiki dump with
     * a `Memory Alpha` site name.
     *
     * @param string Local filename path
     * @return boolean True or false
     */
    public static function validate($filename)
    {
        $isValid = false;

        // Dumb check to verify the file is a database dump from Memory Alpha
        // by checking for a `sitename` tag like the following:
        //     <sitename>Memory Alpha</sitename>
        $streamer = XmlStringStreamer::createStringWalkerParser($filename);
        while ($node = $streamer->getNode()) {
            $xmlNode = simplexml_load_string($node);
            if (isset($xmlNode->sitename)) {
                $siteName = (string) $xmlNode->sitename;
                if (trim($siteName) == 'Memory Alpha') {
                    $isValid = true;
                    break;
                }
            }
        }
        unset($streamer);

        return $isValid;
    }

    /**
     * Import data from a Memory Alpha database dump file into the database.
     *
     * @param string $filename Local filename path
     * @return array Statistics of the import process when completed, in the
     *               following format:
     *
     *                   [
     *                       'duration' => (int)   // Duration of import in seconds,
     *                       'counts'   => (array) // Associative array containing counts of records imported, keys are section (i.e. episode, individual, etc.)     
     *                   ]
     *
     *               Example:
     *
     *                   [
     *                       'duration' => 120,
     *                       'counts'   => [
     *                           'episode' => 122,
     *                       ],
     *                   ]
     *
     *                   The import process took 120 seconds and imported 122 episodes.
     */
    public static function import($filename)
    {
        // Locally cache known series so we can easily map them to episodes.
        // We can parse the series from the file but we would have to perform
        // two passes of the import file since there's no guarantee that
        // series were exported before episodes.
        //
        // Series are automatically seeded on `php artisan db:seed`
        $series = Series::all()->pluck('id', 'abbreviation');

        $counts = ['episodes' => 0];

        $pagesToIgnoreRegex = sprintf('/^(%s)/', implode('|', [
            'Memory Alpha:', 'Help:', 'User:', 'File:', 'User talk:', 'Talk:',
            'Memory Alpha talk:', 'Template:', 'File talk:', 'Category:',
            'Category talk:', 'Forum:', 'Help talk:', 'Template talk:',
            'Portal:', 'Portal talk:',
        ]));

        $begin = time();

        $streamer = XmlStringStreamer::createStringWalkerParser($filename);
        while ($node = $streamer->getNode()) {
            $xmlNode = simplexml_load_string($node);

            if (!isset($xmlNode->title)) {
                continue;
            }

            $title = (string) $xmlNode->title;
            $title = trim(str_replace(['(episode)'], '', $title));
            if (preg_match($pagesToIgnoreRegex, $title)) {
                continue;
            }

            $content = $xmlNode->revision->text;
            $details = null;

            try {
                $details = self::parseXmlNode($content);
            } catch (Exception $ex) {
                continue;
            }

            switch ($details['type']) {
            //
            // Handle episodes
            //
            case 'episode':
                if (!$series->has($details['sSeries'])) {
                    continue;
                }

                // NOTE: Multi-part episodes may not have an integer for its
                //       episode number. For example, VOY: Caretaker (s1e01) is
                //       a two-parter but only has one entry with
                //       `nEpisode = 01/02`; the next episode VOY: Parallax is
                //       `nEpisode = 3` (s1e03).

                Episode::create([
                    'title'         => $title,
                    'series_id'     => $series->get($details['sSeries']),
                    'season_num'    => (int) $details['nSeason'],
                    'episode_num'   => (int) $details['nEpisode'],
                    'serial_number' => $details['sProductionSerialNumber'],
                    'air_date'      => $details['nSerialAirdate'],
                ]);
                $counts['episodes']++;
                break;
            }

            unset($xmlNode, $content, $details);
        }

        $end = time();

        return [
            'duration' => $end - $begin,
            'counts'   => $counts,
        ];
    }

    /**
     * Parse an XML page node, specifically the sidebar section as it
     * contains an easy to parse summary of the page, which is *much*
     * easier than parsing the actual contents of the page.
     *
     * @param string $xml XML node contents
     * @return array Map of data
     */
    private static function parseXmlNode(&$xml)
    {
        // determine type of content based on sidebar type
        $regex = "/\{\{sidebar (?P<type>[a-zA-Z0-9]+)(?P<fields>.*)\n\}\}/s";
        if (!preg_match($regex, $xml, $matches)) {
            throw new Exception('Could not parse XML: Could not find sidebar section.');
        }

        $stripWikiLinks = function($value) {
            return is_array($value)
                ? array_map(function($item) { return trim(str_replace(['[[', ']]'], '', $item)); }, $value)
                : trim(str_replace(['[[', ']]'], '', $value));
        };

        $data = ['type' => $matches['type']];
        $items = array_map('trim', explode('|', $matches['fields']));
        foreach ($items as $item) {
            // only grab key value pairs ($field = $value)
            if (strlen($item) == 0 || strpos($item, '=') === false) {
                continue;
            }

            list($field, $value) = explode('=', $item);
            $field = trim($field);
            $value = trim($value);

            // rearrange date fields to YYYY-mm-dd format
            if (stripos($field, 'date') > 0 && strlen($value) == 8) {
                $value = preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})/', '$1-$2-$3', $value);
            }
            // fields beginning with 'ws' can contain multiple values
            if (substr($field, 0, 2) == 'ws' && strlen($value) > 0) {
                $value = array_filter(array_map('trim', preg_split('/( & | and | &amp; )/', $value)));
            }
            // values can contain HTML comments so strip them
            //$value = preg_replace('/(.*)\s*<!--(.*?)/', '$1', $value);

            $data[$field] = $stripWikiLinks($value);
        }

        // Some serial dates aren't complete so piece it together by its parts
        if (isset($data['nSerialAirdate']) && strlen($data['nSerialAirdate']) != 10) {
            foreach (['Release', 'Airdate'] as $type) {
                if (!isset($data['n'.$type.'Year'])) {
                    continue;
                }

                $dt = new Carbon(sprintf('%s %s %s',
                    $data['s'.$type.'Month'],
                    $data['n'.$type.'Day'],
                    $data['n'.$type.'Year']
                ));
                $data['nSerialAirdate'] = $dt->toDateString();
            }
        }

        return $data;
    }
}
