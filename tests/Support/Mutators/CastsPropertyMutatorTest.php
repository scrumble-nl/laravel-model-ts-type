<?php

declare(strict_types=1);

namespace Tests\Support\Mutators;

use Tests\Models\Bar;
use Tests\Models\Foo;
use Illuminate\Console\Command;
use PHPUnit\Framework\TestCase;
use Scrumble\TypeGenerator\Support\Mutators\CastsPropertyMutator;

/**
 * @internal
 */
class CastsPropertyMutatorTest extends TestCase
{
    public function test_undefined_attributes_raise_warning()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->exactly(2))
            ->method('warn')
            ->withConsecutive(
                ['Skipped property: Undefined property "yesterday" found in casts of model Tests\\Models\\Bar.'],
                ['Skipped property: Undefined property "my_list" found in casts of model Tests\\Models\\Foo.']
            );

        $mutator = new CastsPropertyMutator($command);

        $barDefinition = [
            'today' => [
                'operator' => ':',
                'value' => 'any',
            ],
            'theme' => [
                'operator' => ':',
                'value' => 'any',
            ],
        ];
        $mutator->mutate(new Bar(), $barDefinition);

        $fooDefinition = [
            'total' => [
                'operator' => '?:',
                'value' => 'any',
            ],
        ];
        $mutator->mutate(new Foo(), $fooDefinition);
    }
}
