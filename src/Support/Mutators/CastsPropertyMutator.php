<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Mutators;

use ReflectionException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyMutator;

class CastsPropertyMutator implements IPropertyMutator
{
    /**
     * @var Command
     */
    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

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
            if (false === isset($propertyDefinition[$key])) {
                $this->command->warn(
                    sprintf('Skipped property: Undefined property "%s" found in casts of model %s.', $key, get_class($model))
                );

                continue;
            }
            $propertyDefinition[$key]['value'] = $this->formatCastValue($castValue);
        }
    }

    /**
     * Formate the cast value.
     *
     * @param  string              $castValue
     * @throws ReflectionException
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
            'string /* Date */' => CastsConsts::DATE_TYPES,
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

        if (enum_exists($castValue)) {
            return extractEnumShortName($castValue);
        }

        return $type;
    }
}
