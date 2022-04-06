<?php

namespace Tests\Unit;

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
     */
    public function relative_path()
    {
        $modelPath = './Tests/Models/Bar.php';
        $namespace = FormatNamespace::get($modelPath);
        $this->assertEquals('Tests\\Models\\Bar', $namespace);
    }

}