<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @internal
 */
class EnumTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected bool $deleteFiles = false;

    /**
     * @var string[]
     */
    protected array $modelList = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->modelList = ['test-enum', 'another-enum'];
    }

    /** @test */
    public function can_generate_enums()
    {
        $this->runCommand('', __DIR__ . '/../Models/Enums');

        $content = File::get(__DIR__ . '/../Output/test-enum.d.ts');
        $expected = File::get(__DIR__ . '/Expected/test-enum.d.ts');

        $this->assertEquals($expected, $content);

        unlink(__DIR__ . '/../Output/test-enum.d.ts');
    }

    /** @test */
    public function can_generate_enums_without_prefix()
    {
        $this->runCommand('', __DIR__ . '/../Models/Enums');

        $content = File::get(__DIR__ . '/../Output/another-enum.d.ts');
        $expected = File::get(__DIR__.'/Expected/another-enum.d.ts');

        $this->assertEquals($expected, $content);

        unlink(__DIR__ . '/../Output/another-enum.d.ts');
    }

    /** @test */
    public function models_reference_enum()
    {
        $this->modelList = ['bar'];
        $this->runCommand('--model=Tests\\\\Models\\\\Bar');

        $content = File::get(__DIR__ . '/../Output/bar.d.ts');
        $expected = File::get(__DIR__ . '/Expected/bar.d.ts');

        $this->assertEquals($expected, $content);
    }
}
