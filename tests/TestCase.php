<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;
use Scrumble\TypeGenerator\ServiceProvider;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    /**
     * Setting up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * Register the package providers.
     *
     * @param $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Setup the database for testing.
     *
     * @param  Application  $app
     * @return void
     */
    protected function setUpDatabase(Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('bar', function (Blueprint $table) {
            $table->id();
            $table->date('today');
            $table->datetime('yesterday');
            $table->string('theme');
        });

        $app['db']->connection()->getSchemaBuilder()->create('foo', function (Blueprint $table) {
            $table->id();
            $table->integer('total');
            $table->string('my_list');
        });
    }
}