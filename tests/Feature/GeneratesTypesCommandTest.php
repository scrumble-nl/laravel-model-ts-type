<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Tests\TestCase;

/**
 * @internal
 */
class GeneratesTypesCommandTest extends TestCase
{
    /**
     * @var string[]
     */
    protected array $modelList = ['bar', 'foo', 'foo-bar'];

    /**
     * @test
     * @throws Exception
     */
    public function command_absolute_path() {
        $this->runCommand();
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_namespace(): void
    {
        $this->runCommand('--namespace=Tests\\Models');
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_no_kebab_case(): void
    {
        $this->runCommand('--noKebabCase');
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_model(): void
    {
        $this->modelList = ['foo'];
        $modelPath = addslashes('--model=Tests\\Models\\Foo');

        $this->runCommand($modelPath);
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function not_a_model(): void
    {
        $modelDir = __DIR__ . '/../Models';
        $tempFile = $modelDir . '/TestTrait.php';

        $this->reloadApplication();
        $this->assertFileExists($tempFile);
        $this->runCommand();
    }
}
