<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Illuminate\Support\Str;
use Tests\Models\Enums\ETestEnum;
use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Scrumble\TypeGenerator\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @internal
 */
class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * @var bool
     */
    protected bool $deleteFiles = true;

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
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @param  Application $app
     * @return void
     */
    protected function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('bar', function (Blueprint $table) {
            $table->id();
            $table->date('today');
            $table->datetime('yesterday');
            $table->string('theme');
            $table->enum('test', ETestEnum::values())->default(ETestEnum::FIRST->value);
        });

        $app['db']->connection()->getSchemaBuilder()->create('foo', function (Blueprint $table) {
            $table->id();
            $table->integer('total');
            $table->string('my_list');
        });

        $app['db']->connection()->getSchemaBuilder()->create('cast_functions', function (Blueprint $table) {
            $table->id();
            $table->string('foo_id');
            $table->string('bar_id');
        });
    }

    /**
     * @param  string $kebabCase
     * @param  string $commandAddon
     * @return string
     */
    protected function replaceToCamel(string $kebabCase, string $commandAddon): string
    {
        if (!Str::contains($commandAddon, '--noKebabCase')) {
            return $kebabCase;
        }

        return Str::ucfirst(Str::camel($kebabCase));
    }

    /**
     * @param  string    $addOnToCommand
     * @param  string    $modelDir
     * @param  string    $outputDir
     * @throws Exception
     * @return void
     */
    protected function runCommand(
        string $addOnToCommand = '',
        string $modelDir = __DIR__ . '/Models',
        string $outputDir = __DIR__ . '/Output',
    ): void {
        $realPath = realpath($modelDir);

        if (!$realPath) {
            throw new Exception('Could not found the path \'' . $modelDir . '\'', 404);
        }

        foreach ($this->modelList as $modelName) {
            $modelName = $this->replaceToCamel($modelName, $addOnToCommand);
            $outputFile = $outputDir . '/' . $modelName . '.d.ts';

            if (file_exists($outputFile)) {
                @unlink($outputFile);
            }
        }

        $this->artisan("types:generate --modelDir={$modelDir} --outputDir={$outputDir} {$addOnToCommand}")
            ->assertExitCode(0);

        $this->reloadApplication();

        foreach ($this->modelList as $modelName) {
            $modelName = $this->replaceToCamel($modelName, $addOnToCommand);
            $outputFile = $outputDir . '/' . $modelName . '.d.ts';

            $this->assertFileExists($outputFile);

            if ($this->deleteFiles) {
                @unlink($outputFile);
            }
        }
    }
}
