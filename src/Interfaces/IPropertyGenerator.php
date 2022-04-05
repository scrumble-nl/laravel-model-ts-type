<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface IPropertyGenerator
{
    /**
     * Get the property definition for the given model.
     *
     * @param  Model $model
     * @return array
     */
    public function getPropertyDefinition(Model $model): array;
}
