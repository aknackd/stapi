<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use App\MemoryAlphaDatabaseFile;
use App\Models\Episode;
use App\Helpers\EnvironmentHelper;

class StapiImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stapi:import
        {--cache-dir= : Specify directory where the Memory Alpha XML dump is cached}
        {--file=      : Specify Memory Alpha dump to import}
        {--progress   : Show progress bar when downloading the Memory Alpha XML dump file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from Memory Alpha';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filesToCleanup = [];
        $cacheDir = $this->option('cache-dir') ?: EnvironmentHelper::getUserCacheDirectory('stapi');
        $filename = null;
        $doDownload = false;
        $doExtract = true;
        $progressBar = null;

        // Cannot specify --cache-dir and --file at the same time
        if ($this->option('cache-dir') && $this->option('file')) {
            throw new InvalidOptionException('Cannot set --cache-dir and --file at the same time');
        }
        // Cannot specify --file and --progress at the same time
        if ($this->option('file') && $this->option('progress')) {
            throw new InvalidOptionException('Cannot set --file and --progress at the same time');
        }
        // Verify cache directory exists, if not then create it
        if (!file_exists($cacheDir)) {
            if (!mkdir($cacheDir, 0755, true)) {
                throw new InvalidOptionException('Unable to create cache directory: '.$cacheDir);
            }
        }

        if ($this->option('file')) {
            // Use specified file using --file= option
            $filename = $this->option('file');
            if (!file_exists($filename) || !is_readable($filename)) {
                throw new InvalidOptionException('Specified file not found: '.$filename);
            }
            $importFile = $filename;
        } else {
            // Check the cache dir, set download flag if file is not found
            $filename = MemoryAlphaDatabaseFile::localFile($cacheDir);
            if (!file_exists($filename) || !is_readable($filename)) {
                $doDownload = true;
                $doExtract = true;
                if ($this->option('progress')) {
                    $filesize = MemoryAlphaDatabaseFile::size();
                    $progressBar = $this->output->createProgressBar($filesize / 1024);
                }
            }
        }

        // Download database dump
        if ($doDownload) {
            $filename = $this->downloadDatabaseDump($cacheDir, $progressBar);
        }

        // Extract database dump (if applicable)
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension == '7z') {
            $importFile = $this->extractDatabaseDump($filename, $cacheDir);
            $filesToCleanup[] = $importFile;
        } else if ($extension == 'xml') {
            $importFile = $filename;
        } else {
            throw new InvalidOptionException(
                'Invalid database dump found, must be either a 7-zip archive or XML file: '.$filename);
        }

        // Finally import and cleanup after ourselves
        $this->importDatabaseDump($importFile);
        $this->postImportCleanup($filesToCleanup);
    }

    /**
     * Downloads the remote database dump from Memory Alpha.
     *
     * @param string $downloadDir Directory to download file into.
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar Progress bar to display while downloading.
     * @return string Path to downloaded file.
     */
    private function downloadDatabaseDump($downloadDir, ProgressBar $progressBar = null)
    {
        $this->info('Downloading Memory Alpha database dump to cache');

        return MemoryAlphaDatabaseFile::download($downloadDir, $progressBar);
    }

    /**
     * Extract a Memory Alpha database dump.
     *
     * @param string $filename  Memory Alpha database dump local filename
     * @param string $outputDir Directory to extract contents to
     * @return string Local filename path to extracted Memory Alpha database dump
     */
    private function extractDatabaseDump($filename, $outputDir)
    {
        $this->info('Extracting Memory Alpha database dump');

        return MemoryAlphaDatabaseFile::extract($filename, $outputDir);
    }

    /**
     * Import Memory Alpha database dump.
     *
     * @param string $filename Memory Alpha database dump local filename
     */
    private function importDatabaseDump($filename)
    {
        $this->info('Importing Memory Alpha database dump');

        // Validate file first
        if (!MemoryAlphaDatabaseFile::validate($filename)) {
            $this->error('File failed validation, import canceled');
            return;
        }

        // Continue with import
        $stats = MemoryAlphaDatabaseFile::import($filename);

        $this->info('Import process completed, took '.$stats['duration'].' seconds');
        
        // Display import counts by section
        foreach ($stats['counts'] as $section => $num) {
            $this->info('  - Found '.$num.' '.str_plural($section));
        };
    }

    /**
     * Perform post-import cleanup tasks.
     *
     * @param array $files Files to delete
     */
    private function postImportCleanup(array $files = [])
    {
        $this->info('Performing post import cleanup tasks');

        foreach ($files as $filename) {
            if (!unlink($filename)) {
                throw new RuntimeException('Failed to delete file: '.$filename);
            }
        }
    }
}
