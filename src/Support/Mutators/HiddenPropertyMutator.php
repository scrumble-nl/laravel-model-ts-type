<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Mutators;

use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyMutator;

class HiddenPropertyMutator implements IPropertyMutator
{
    /**
     * {@inheritDoc}
     */
    public function mutate(Model $model, array &$propertyDefinition): void
    {
        $reflectionClass = new \ReflectionClass($model);
        $hiddenProperty = $reflectionClass->getProperty('hidden');
        $hiddenProperty->setAccessible(true);
        $hiddenFields = $hiddenProperty->getValue($model);

        foreach ($hiddenFields as $hiddenField) {
            unset($propertyDefinition[$hiddenField]);
        }
    }
}
