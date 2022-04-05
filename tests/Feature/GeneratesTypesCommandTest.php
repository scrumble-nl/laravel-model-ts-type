<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeneratesTypesCommandTest extends TestCase
{
    /**
     * The model list to test.
     *
     * @var string[]
     */
    protected $modelList = ['bar', 'foo'];

    /**
     * Test for the general generate command.
     *
     * @return void
     */
    public function test_command_absolute_path()
    {
        $modelDir  = __DIR__.'/../Models';
        $outputDir = __DIR__.'/../Output';

        // Check if output is already existing
        foreach ($this->modelList as $modelName) {
            $outputFile = $outputDir.'/'.$modelName.'.d.ts';

            if (file_exists($outputFile)) {
                error_log('The output file for '.$modelName.' does already exist.');
            }
        }

        // Try to run the command
        $this->artisan("types:generate --modelDir={$modelDir} --outputDir={$outputDir}")
            ->assertExitCode(0);

        // Reload app to receive new files in cache
        $this->reloadApplication();

        // Check if the generated files exist
        foreach ($this->modelList as $modelName) {
            $outputFile = $outputDir.'/'.$modelName.'.d.ts';

            $this->assertFileExists($outputFile);
        }
    }
}
