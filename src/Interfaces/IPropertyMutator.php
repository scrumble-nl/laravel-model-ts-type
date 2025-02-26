<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Interfaces;

use ReflectionException;
use Illuminate\Database\Eloquent\Model;

interface IPropertyMutator
{
    /**
     * Mutate the given property definition for the given model.
     *
     * @param  Model                $model
     * @param  array<array-key, array<array-key, mixed>>                $propertyDefinition
     * @throws ReflectionException
     */
    public function mutate(Model $model, array &$propertyDefinition): void;
}
