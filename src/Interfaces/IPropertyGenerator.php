<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Interfaces;

interface IPropertyGenerator
{
    /**
     * Get the property definition for the given model
     *
     * @param  $model
     * @return array
     */
    public function getPropertyDefinition($model): array;
}
