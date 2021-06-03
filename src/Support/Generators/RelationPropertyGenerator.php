<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class RelationPropertyGenerator implements IPropertyGenerator
{
    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition(Model $model): array
    {
        $propertyDefinition = [];
        $reflectionClass = new \ReflectionClass($model);
        $withProperty = $reflectionClass->getProperty('with');
        $withProperty->setAccessible(true);
        $withFields = $withProperty->getValue($model);

        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->class === get_class($model)) {
                // FIXME: if there only is docblock available, make sure it works for unqualified names aswell
                $returnType = $this->getReturnType($method);

                if (strpos($returnType, 'Illuminate\Database\Eloquent\Relations') !== false) {
                    $methodName = $method->getName();
                    $relatedClassSegments = explode('\\', get_class($model->$methodName()->getRelated()));

                    // TODO: In later stage fix relations for packagized models
                    if ('App' === $relatedClassSegments[0]) {
                        $relatedClass = end($relatedClassSegments);
                        $snakeCase = $reflectionClass->getProperty('snakeAttributes')->getValue($model);

                        $propertyDefinition[$snakeCase ? snake_case($methodName) : $methodName] = [
                            'operator' => in_array($methodName, $withFields) ? ':' : '?:',
                            'value' => $this->formatValue($relatedClass, $returnType)
                        ];
                    }
                }
            }
        }

        return $propertyDefinition;
    }

    /**
     * Get return type based on typing or doc block
     *
     * @param  \ReflectionMethod $method
     * @return string
     */
    private function getReturnType(\ReflectionMethod $method): string
    {
        if (null !== ($returnType = $method->getReturnType())) {
            return $returnType->getName();
        }

        $docComment = $method->getDocComment();

        if (false === $docComment) {
            return '';
        }

        $matches = [];
        preg_match('/(?<=@return ).+/', $docComment, $matches);

        if (isset($matches[0])) {
            return $matches[0];
        }

        return '';
    }

    /**
     * Format the value used for the types
     *
     * @param string $relatedClass
     * @param string $returnType
     * @return string
     */
    private function formatValue(string $relatedClass, string $returnType): string
    {
        if (str_contains($returnType, 'Morph')) {
            $relatedClass = 'any';
        }

        return $relatedClass . (str_contains($returnType, 'Many') ? '[]' : '') . ' | null';
    }
}
