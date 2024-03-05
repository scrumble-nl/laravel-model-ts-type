<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use ReflectionClass;
use ReflectionMethod;
use ReflectionUnionType;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class AttributePropertyGenerator implements IPropertyGenerator
{
    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition(Model $model): array
    {
        $appendFields = [];
        $propertyDefinition = [];
        $reflectionClass = new ReflectionClass($model);
        $appendProperty = $reflectionClass->getProperty('appends');
        $appendProperty->setAccessible(true);

        foreach ($appendProperty->getValue($model) as $attribute) {
            $appendFields['get' . ucfirst(camel_case($attribute)) . 'Attribute'] = $attribute;
        }

        foreach ($reflectionClass->getMethods() as $method) {
            $methodName = $method->getName();

            if (array_key_exists($methodName, $appendFields)
                || ($notAppended = preg_match('/^get[A-Z]{1}[A-z]+Attribute$/', $methodName))
            ) {
                $propertyDefinition[$appendFields[$methodName] ?? $this->formatAttributeName($methodName)] = [
                    'operator' => isset($notAppended) ? '?:' : ':',
                    'value' => $this->getPropertyType($method),
                ];
            }
        }

        return $propertyDefinition;
    }

    /**
     * Format the given mysql field.
     *
     * @param  string $returnType
     * @return string
     */
    public function formatPhpReturnType(string $returnType): string
    {
        switch ($returnType) {
            case 'string':
                return 'string';

            case 'int':
            case 'float':
                return 'number';

            case 'bool':
                return 'boolean';

            case 'array':
                return 'any[]';

            default:
                return 'any';
        }
    }

    /**
     * Format the attribute name based on the camelCase method name.
     *
     * @param  string $methodName
     * @return string
     */
    private function formatAttributeName(string $methodName): string
    {
        return snake_case(preg_replace(['/^get/', '/Attribute$/'], '', $methodName) ?? '');
    }

    /**
     * Get the js type for the given method.
     *
     * @param  ReflectionMethod $method
     * @return string
     */
    private function getPropertyType(ReflectionMethod $method): string
    {
        if (null !== ($returnType = $method->getReturnType())) {
            if($returnType instanceof ReflectionUnionType) {
                return collect($returnType->getTypes())
                    ->map(function($returnType) {
                        return $this->formatPhpReturnType($returnType->getName()) ;
                    })
                    ->join(' | ') . ($returnType->allowsNull() ? ' | null' : '');
            }

            // @phpstan-ignore-next-line
            return $this->formatPhpReturnType($returnType->getName()) . ($returnType->allowsNull() ? ' | null' : '');
        }

        $docComment = $method->getDocComment();
        if (!$docComment) {
            return 'any';
        }

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

            return implode(' | ', $jsTypes) . (in_array('null', $types) ? ' | null' : '');
        }

        return 'any';
    }
}
