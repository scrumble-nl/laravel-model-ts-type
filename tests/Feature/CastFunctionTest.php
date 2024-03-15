<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Application;

/**
 * @internal
 */
class CastFunctionTest extends TestCase
{
    /**
     * @var string[]
     */
    protected array $modelList = ['cast-function'];

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_model(): void
    {
        if (starts_with(Application::VERSION, '10.')) {
            $this->markTestSkipped();
        }

        $this->deleteFiles = false;
        $modelPath = addslashes('--model=Tests\\Models\\CastFunction');

        $this->runCommand($modelPath);

        $expectedOut = File::get(__DIR__ . '/Expected/cast-function.d.ts');
        $output = File::get(__DIR__ . '/../Output/cast-function.d.ts');

        $this->assertEquals($expectedOut, $output);
    }
}
