<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class RelationPropertyGenerator implements IPropertyGenerator
{
    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition($model): array
    {
        $propertyDefinition = [];
        $reflectionClass = new \ReflectionClass($model);
        $withProperty = $reflectionClass->getProperty('with');
        $withProperty->setAccessible(true);
        $withFields = $withProperty->getValue($model);

        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->class === get_class($model) && $method->hasReturnType()) {
                $returnType = $method->getReturnType()->getName();

                if (strpos($returnType, 'Illuminate\Database\Eloquent\Relations') !== false) {
                    // TODO: Should all relations always be nullable?
                    $methodName = $method->getName();
                    $relatedClassSegments = explode('\\', get_class($model->$methodName()->getRelated()));
                    $relatedClass = end($relatedClassSegments);
                    $propertyDefinition[snake_case($methodName)] = [
                        'operator' => in_array($methodName, $withFields) ? ':' : '?:',
                        'value' =>$relatedClass . (strpos($returnType, 'Many') !== false ? '[]' : '')
                    ];
                }
            }
        }

        return $propertyDefinition;
    }
}
