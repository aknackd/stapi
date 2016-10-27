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
use App\Models\Species;
use App\Models\Starship;
use App\Models\StarshipClass;

/**
 * Represents a Memory Alpha database dump file.
 */
final class MemoryAlphaDatabaseFile
{
    /**
     * Location of the current database dump from Memory Alpha.
     * Note that Memory Alpha doesn't keep past archives, it only keeps the
     * most recent backups (no idea how often or when backups are made).
     *
     * @var string
     */
    const DATABASE_DUMP_URL = 'https://s3.amazonaws.com/wikia_xml_dumps/e/en/enmemoryalpha_pages_current.xml.7z';

    /**
     * List of pages to skip during the import process. Mostly internal
     * MediaWiki pages we don't care about.
     *
     * @var array
     */
    const PAGES_TO_IGNORE = [
        'Memory Alpha:', 'Help:', 'User:', 'File:', 'User talk:', 'Talk:',
        'Memory Alpha talk:', 'Template:', 'File talk:', 'Category:',
        'Category talk:', 'Forum:', 'Help talk:', 'Template talk:',
        'Portal:', 'Portal talk:',
    ];

    /**
     * List of categories the import process handles. Used for keeping counts
     * of records imported by category for displaying import statistics.
     *
     * NOTE: Update this with every new category being handled by `import()`.
     *
     * @var array
     */
    const IMPORT_CATEGORIES = ['episode', 'species', 'starship class', 'starship'];

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
                $siteName = trim((string) $xmlNode->sitename);
                if ($siteName == 'Memory Alpha') {
                    $isValid = true;
                    break;
                }
            }
            unset($xmlNode);
        }
        unset($streamer);

        return $isValid;
    }

    /**
     * Import data from a Memory Alpha database dump file into the database.
     *
     * @param string $filename Local filename path
     * @return array Statistics of the import process when completed, in the following format:
     *
     *     [
     *       'duration' => (int)   // Duration of import in seconds,
     *       'counts'   => (array) // Array containing counts of records imported, keys are section (i.e. episodes, individuals, etc.)
     *     ]
     *
     *     Example:
     *
     *     [
     *       'duration' => 120,
     *       'counts'   => [
     *           'episodes'  => 122,
     *        ],
     *     ]
     *
     *     In this example, the import process took 120 seconds and imported 122 episodes.
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

        // Strip categories from the page title as a suffix
        // (i.e. if page title is "First Contact (episode)", remove "(episode)")
        $pageSuffixesToStrip = collect(self::IMPORT_CATEGORIES)
            ->map(function ($item, $key) { return "({$item})"; })
            ->toArray();

        $counts = array_fill_keys(array_map('str_plural', self::IMPORT_CATEGORIES), 0);
        $begin  = time();

        // Parse database dump XML one node at a time
        $streamer = XmlStringStreamer::createStringWalkerParser($filename);
        while ($node = $streamer->getNode()) {
            // 1) Parse XML node and grab the page title
            if (($xmlNode = simplexml_load_string($node)) === false) {
                logger()->error('import: Could not parse XML');
                continue;
            }
            if (!isset($xmlNode->title)) {
                logger()->error('import: Could not find title in XML');
                continue;
            }

            // 2) Remove category from title if it's a suffix
            $title = (string) $xmlNode->title;
            $title = trim(str_replace($pageSuffixesToStrip, '', $title));

            // 3) Skip pages we explicitly want to ignore
            if (starts_with($title, self::PAGES_TO_IGNORE)) {
                //logger()->debug('import: Skipping page', ['title' => $title]);
                continue;
            }

            // 4) Fetch page contents and parse the sidebar to get the page metadata
            $content = $xmlNode->revision->text;
            $data = compact('title');

            try {
                $data = array_merge($data, MemoryAlphaSidebar::parse($content));
            } catch (Exception $ex) {
                logger()->warning('import: Could not parse page sidebar', [
                    'error' => $ex->getMessage()]);
                continue;
            }

            // 5) Import (create) database record
            switch ($data[MemoryAlphaSidebar::TYPE_KEY]) {
            //
            // Handle episodes
            //
            case 'episode':
                if (!$series->has($data['sSeries'])) {
                    logger()->error('Invalid series found', [
                        'series' => $data['sSeries']]);
                    continue;
                }
                $data = array_merge($data, [
                    'series_id' => $series->get($data['sSeries'])]);

                Episode::import($data);
                $counts['episodes']++;
                break;
            //
            // Handle species
            //
            case 'species':
                Species::import($data);
                $counts['species']++;
                break;
            //
            // Handle starships
            //
            case 'starship':
                Starship::import($data);
                $counts['starships']++;
                break;

            case 'starship class':
                StarshipClass::import($data);
                $counts['starship classes']++;
                break;
            //
            // Unhandled type encountered
            //
            default:
                continue;
                logger()->info('import: Unhandled category', [
                    'type' => $data['__type__']]);
                break;
            }
            unset($xmlNode, $content, $data);
        }
        unset($streamer);

        $end = time();

        return [
            'duration' => $end - $begin,
            'counts'   => $counts,
        ];
    }
}
