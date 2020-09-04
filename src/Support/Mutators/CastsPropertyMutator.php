<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Mutators;

use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyMutator;

class CastsPropertyMutator implements IPropertyMutator
{
    /**
     * {@inheritDoc}
     */
    public function mutate(Model $model, array &$propertyDefinition): void
    {
        $reflectionClass = new \ReflectionClass($model);
        $castsProperty = $reflectionClass->getProperty('casts');
        $castsProperty->setAccessible(true);
        $castFields = $castsProperty->getValue($model);

        foreach ($castFields as $key => $castValue) {
            $propertyDefinition[$key]['value'] = $this->formatCastValue($castValue);
        }
    }

    /**
     * Formate the cast value
     *
     * @param string $castValue
     * @return string
     */
    private function formatCastValue(string $castValue): string
    {
        $type = 'any';
        $typesToCheck = [
            'boolean' => CastsConsts::BOOL_TYPES,
            'string' => CastsConsts::STRING_TYPES,
            'number' => CastsConsts::NUMBER_TYPES,
            'any[]' => CastsConsts::ARRAY_TYPES,
            'string /* Date */' => CastsConsts::DATE_TYPES
        ];

        foreach ($typesToCheck as $tsType => $typesToCheck) {
            foreach ($typesToCheck as $castType) {
                if (false !== strpos($castValue, $castType)) {
                    $type = $tsType;
                    break;
                }
            }

            if ('any' !== $type) {
                break;
            }
        }

        return $type;
    }
}
