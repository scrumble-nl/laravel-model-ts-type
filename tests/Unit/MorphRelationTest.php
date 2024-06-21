<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Scrumble\TypeGenerator\Facades\FormatNamespace;
use Scrumble\TypeGenerator\Support\Generators\RelationPropertyGenerator;
use Tests\Models\Foo;
use Tests\TestCase;

/**
 * @internal
 */
class MorphRelationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var RelationPropertyGenerator
     */
    private RelationPropertyGenerator $relationPropertyGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->relationPropertyGenerator = new RelationPropertyGenerator();
    }

    /**
     * @test
     * @return void
     */
    public function can_get_type_for_morph_one(): void
    {
        $result = $this->relationPropertyGenerator->formatValue('Morph', 'Illuminate\Database\Eloquent\Relations\MorphOne');

        $this->assertEquals('Morph | null', $result);
    }

    /**
     * @test
     * @return void
     */
    public function can_get_type_for_morph_many(): void
    {
        $result = $this->relationPropertyGenerator->formatValue('Morph', 'Illuminate\Database\Eloquent\Relations\MorphMany');

        $this->assertEquals('Morph[] | null', $result);
    }

    /**
     * @test
     * @return void
     */
    public function returns_any_for_morph_to(): void
    {
        $result = $this->relationPropertyGenerator->formatValue('Foo', 'Illuminate\Database\Eloquent\Relations\MorphTo');

        $this->assertEquals('any | null', $result);
    }

    /**
     * @test
     * @return void
     */
    public function returns_pivot_table_for_morph_to_many(): void
    {
        $result = $this->relationPropertyGenerator->formatValue('Foo', 'Illuminate\Database\Eloquent\Relations\MorphToMany');

        $this->assertEquals('Foo[] | null', $result);
    }
}
