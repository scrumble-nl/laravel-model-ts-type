<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Str;

/**
 * @internal
 */
class GeneratesTypesCommandTest extends TestCase
{
    /**
     * @var string[]
     */
    protected $modelList = ['bar', 'foo', 'foo-bar'];

    /**
     * @test
     * @param  mixed     $modelDir
     * @param  mixed     $outputDir
     * @throws Exception
     */
    public function command_absolute_path(
        string $addOnToCommand = '',
        string $modelDir = __DIR__ . '/../Models',
        string $outputDir = __DIR__ . '/../Output'
    ) {
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
            @unlink($outputFile);
        }
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_namespace()
    {
        $this->command_absolute_path('--namespace=Tests\\Models');
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_no_kebab_case()
    {
        $this->command_absolute_path('--noKebabCase');
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function command_option_model()
    {
        $this->modelList = ['foo'];
        $modelPath = addslashes('--model=Tests\\Models\\Foo');

        $this->command_absolute_path($modelPath);
    }

    /**
     * @test
     * @throws Exception
     * @return void
     */
    public function not_a_model()
    {
        $modelDir = __DIR__ . '/../Models';
        $tempFile = $modelDir . '/TestTrait.php';
        $tempFileContent = <<<'EOD'
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

    /**
     * @param  string  $kebabCase
     * @param  string  $commandAddon
     * @return string
     */
    protected function replaceToCamel(string $kebabCase, string $commandAddon): string
    {
        if (!Str::contains($commandAddon, '--noKebabCase')) {
            return $kebabCase;
        }

        return Str::ucfirst(Str::camel($kebabCase));
    }
}
