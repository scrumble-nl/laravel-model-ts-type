<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Scrumble\TypeGenerator\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @internal
 */
class TestCase extends Orchestra
{
    use DatabaseMigrations;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
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
     * @param  Application $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param  Application $app
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
