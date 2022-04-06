<?php

namespace Tests\Unit;

use Exception;
use Scrumble\TypeGenerator\Facades\FormatNamespace;
use Tests\TestCase;

class FormatNamespaceTest extends TestCase
{

    /**
     * @test
     * @return void
     */
    public function absolute_path()
    {
        $modelFilePath = __DIR__ . '/../Models/Bar.php';
        $this->assertFileExists($modelFilePath);

        $namespace = FormatNamespace::get($modelFilePath);
        $this->assertEquals('Tests\\Models\\Bar', $namespace);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function weird_path()
    {
        $modelPath = __DIR__ . '/../Support/../Models/Bar.php';
        $modelRealPath = realpath($modelPath);

        if (!$modelRealPath) {
            throw new Exception('Could not find the real path to \'' . $modelPath . '\'');
        }

        $namespace = FormatNamespace::get($modelPath);
        $this->assertEquals('Tests\\Models\\Bar', $namespace);
    }

}