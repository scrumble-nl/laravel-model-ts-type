<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Interfaces;

use Exception;
use Illuminate\Database\Eloquent\Model;

interface IPropertyGenerator
{
    /**
     * Get the property definition for the given model.
     *
     * @param  Model     $model
     * @throws Exception
     * @return array<array-key, array<array-key, mixed>>
     */
    public function getPropertyDefinition(Model $model): array;
}
