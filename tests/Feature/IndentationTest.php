<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @internal
 */
class IndentationTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected bool $deleteFiles = true;

    /**
     * @var string[]
     */
    protected array $modelList = [];

    /** @test */
    public function use_4_spaces_by_default()
    {
        $this->runCommand();

        $content = File::get(__DIR__ . '/../Output/bar.d.ts');
        $expected = File::get(__DIR__ . '/Expected/Indentation/four-spaces-bar.d.ts');

        $this->assertEquals($expected, $content);
    }

    /** @test */
    public function can_generate_enums_without_prefix()
    {
        $this->runCommand('--indentationSpaces=2');

        $content = File::get(__DIR__ . '/../Output/bar.d.ts');
        $expected = File::get(__DIR__ . '/Expected/Indentation/two-spaces-bar.d.ts');

        $this->assertEquals($expected, $content);
    }

    /** @test */
    public function can_use_namespace_with_indentation_spaces()
    {
        $this->runCommand('--indentationSpaces=2 --namespace=true');

        $content = File::get(__DIR__ . '/../Output/bar.d.ts');
        $expected = File::get(__DIR__ . '/Expected/Indentation/two-spaces-namespace-bar.d.ts');

        $this->assertEquals($expected, $content);
    }
}
