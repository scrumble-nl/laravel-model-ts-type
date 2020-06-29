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
                $propertyDefinition[$appendFields[$methodName] ?? $this->formatAttributeName($methodName)] = [
                    'operator' => $notAppended ? '?:' : ':',
                    'value' => $this->getPropertyType($method),
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

    /**
     * Get the js type for the given method
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    private function getPropertyType(\ReflectionMethod $method): string
    {
        if (null !== ($returnType = $method->getReturnType())) {
            return $this->formatPhpReturnType($returnType->getName()) . ($returnType->allowsNull() ? '|null' : '');
        }

        $docComment = $method->getDocComment();
        $matches = [];
        preg_match('/(?<=@return ).+/', $docComment, $matches);

        if (isset($matches[0])) {
            $types = explode('|', $matches[0]);
            $jsTypes = [];

            foreach ($types as $type) {
                if ('null' === $type) {
                    continue;
                }

                $jsTypes[] = $this->formatPhpReturnType($type);
                $jsTypes = array_unique($jsTypes);
            }

            return implode('|', $jsTypes) . (in_array('null', $types) ? '|null' : '');
        }

        return 'any';
    }

    /**
     * Format the given mysql field
     *
     * @param  string $returnType
     * @return string
     */
    public function formatPhpReturnType(string $returnType): string
    {
        $type = 'any';

        switch ($returnType) {
            case 'string':
                $type = 'string';
                break;
            case 'int':
            case 'float':
                $type = 'number';
                break;
            case 'bool':
                $type = 'boolean';
                break;
            case 'array':
                $type = 'any[]';
                break;
        }

        return $type;
    }
}
