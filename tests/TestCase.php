<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use DatabaseMigrations;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $this->setupTestingDatabase();
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    /**
     * Run database migrations prior to each test.
     */
    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    /**
     * Reset the database after each test is completed.
     */
    public function tearDown()
    {
        Artisan::call('migrate:reset');
        parent::tearDown();
    }

    /**
     * Initialize the testing database.
     */
    private function setupTestingDatabase()
    {
        $database = env('DB_DATABASE');
        $destroyCallback = null;

        switch (env('DB_CONNECTION')) {
        case 'sqlite':
            touch($database);
            $destroyCallback = function () use ($database) {
                @unlink($database);
            };
            break;
        }

        if ($destroyCallback !== null) {
            $this->beforeApplicationDestroyed($destroyCallback);
        }
    }
}
