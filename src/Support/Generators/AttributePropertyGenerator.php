<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class AttributePropertyGenerator implements IPropertyGenerator
{
    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition($model): array
    {
        $appendFields = [];
        $propertyDefinition = [];
        $reflectionClass = new \ReflectionClass($model);
        $appendProperty = $reflectionClass->getProperty('appends');
        $appendProperty->setAccessible(true);

        foreach ($appendProperty->getValue($model) as $attribute) {
            $appendFields['get' . ucfirst(camel_case($attribute)) . 'Attribute'] = $attribute;
        }

        foreach ($reflectionClass->getMethods() as $method) {
            $methodName = $method->getName();

            if (array_key_exists($methodName, $appendFields) ||
                ($notAppended = preg_match('/^get[A-Z]{1}[A-z]+Attribute$/', $methodName))
            ) {
                // TODO: handle return type properly
                $propertyDefinition[$appendFields[$methodName] ?? $this->formatAttributeName($methodName)] = [
                    'operator' => $notAppended ? '?:' : ':',
                    'value' => optional($method->getReturnType())->getName() ?? 'any',
                ];
            }
        }

        return $propertyDefinition;
    }

    /**
     * Format the attibute name based on the camelCase method name
     *
     * @param string $methodName
     * @return string
     */
    private function formatAttributeName(string $methodName): string
    {
        return snake_case(preg_replace(['/^get/', '/Attribute$/'], '', $methodName));
    }
}
