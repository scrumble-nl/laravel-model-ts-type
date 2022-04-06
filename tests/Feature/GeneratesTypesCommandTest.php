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
    protected $modelList = ['bar', 'foo'];

    /**
     * @test
     */
    public function command_absolute_path()
    {
        $modelDir = __DIR__ . '/../Models';
        $outputDir = __DIR__ . '/../Output';
        $realPath = realpath($modelDir);

        if (!$realPath) {
            throw new Exception('Could not found the path \'' . $modelDir . '\'', 404);
        }

        foreach ($this->modelList as $modelName) {
            $outputFile = $outputDir . '/' . $modelName . '.d.ts';

            if (file_exists($outputFile)) {
                @unlink($outputFile);
            }
        }

        $this->artisan("types:generate --modelDir={$modelDir} --outputDir={$outputDir}")
            ->assertExitCode(0);

        $this->reloadApplication();

        foreach ($this->modelList as $modelName) {
            $outputFile = $outputDir . '/' . $modelName . '.d.ts';

            $this->assertFileExists($outputFile);
        }
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function not_a_model()
    {
        $modelDir = __DIR__ . '/../Models';
        $tempFile = $modelDir . '/TestTrait.php';
        $tempFileContent = <<<EOD
<?php

namespace Tests\Models;

trait TestTrait {
    public function test() {
        dd('test');
    }
}
EOD;

        $tempFileHandler = fopen($tempFile, 'w');
        fwrite($tempFileHandler, $tempFileContent);
        fclose($tempFileHandler);

        $this->reloadApplication();
        $this->assertFileExists($tempFile);
        $this->command_absolute_path();

        unlink($tempFile);
    }
}
