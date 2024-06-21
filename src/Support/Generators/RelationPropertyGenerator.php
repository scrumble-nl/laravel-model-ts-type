<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Generators;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Interfaces\IPropertyGenerator;

class RelationPropertyGenerator implements IPropertyGenerator
{
    private const RELATION_TYPE = 'Illuminate\Database\Eloquent\Relations';

    private const REFLECTION_RETURN_TYPES = [
        ReflectionNamedType::class,
        ReflectionUnionType::class,
        ReflectionIntersectionType::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function getPropertyDefinition(Model $model): array
    {
        $propertyDefinition = [];
        $reflectionClass = new ReflectionClass($model);
        $withProperty = $reflectionClass->getProperty('with');
        $withProperty->setAccessible(true);
        $withFields = $withProperty->getValue($model);

        $permittedClasses = [get_class($model)];
        foreach(class_parents($model) as $parent) {
            if ($parent === Model::class) {
                break;
            }
            $permittedClasses[] = $parent;
        }

        foreach ($reflectionClass->getMethods() as $method) {
            if (in_array($method->class, $permittedClasses)) {
                // FIXME: if there only is docblock available, make sure it works for unqualified names aswell
                $returnType = $this->getReturnType($method);

                if ($returnType === null) {
                    continue;
                }

                $relationReturn = array_first($returnType, fn ($type) => str_contains($type, self::RELATION_TYPE));

                if ($relationReturn) {
                    $methodName = $method->getName();
                    // @phpstan-ignore-next-line
                    $relatedClassSegments = explode('\\', get_class($model->{$methodName}()->getRelated()));

                    // TODO: In later stage fix relations for packagized models
                    if ('App' === $relatedClassSegments[0]) {
                        $relatedClass = end($relatedClassSegments);
                        $snakeCase = $reflectionClass->getProperty('snakeAttributes')->getValue($model);

                        $propertyDefinition[$snakeCase ? snake_case($methodName) : $methodName] = [
                            'operator' => in_array($methodName, $withFields) ? ':' : '?:',
                            'value' => $this->formatValue($relatedClass, $relationReturn),
                        ];
                    }
                }
            }
        }

        return $propertyDefinition;
    }

    /**
     * Get return type based on typing or doc block.
     *
     * @param ReflectionMethod $method
     * @return null|array<int, string>
     */
    private function getReturnType(ReflectionMethod $method): ?array
    {
        if (null !== ($returnType = $method->getReturnType())) {
            if (in_array($returnType::class, self::REFLECTION_RETURN_TYPES)) {
                /** @phpstan-ignore-next-line: if it gets here the type is definitely correct */
                return $this->parseReflectionReturnType($returnType);
            }
        }

        $docComment = $method->getDocComment();

        if (false === $docComment) {
            return null;
        }

        $matches = [];
        preg_match('/(?<=@return ).+/', $docComment, $matches);

        if (isset($matches[0])) {
            return [$matches[0]];
        }

        return null;
    }

    /**
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType $returnType
     * @return null|array<int, string>
     */
    private function parseReflectionReturnType(ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType $returnType): ?array
    {
        if ($returnType instanceof ReflectionIntersectionType) {
            return null;
        }

        if ($returnType instanceof ReflectionUnionType) {
            $returnTypeNames = [];

            foreach ($returnType->getTypes() as $returnType) {
                $returnTypeNames[] = $this->parseReflectionReturnType($returnType);
            }

            return array_flatten($returnTypeNames);
        }

        return [$returnType->getName()];
    }

    /**
     * Format the value used for the types.
     *
     * @param string $relatedClass
     * @param string $returnType
     * @return string
     */
    public function formatValue(string $relatedClass, string $returnType): string
    {
        if (str_ends_with($returnType, 'MorphTo')) {
            $relatedClass = 'any';
        }

        return $relatedClass . (str_contains($returnType, 'Many') ? '[]' : '') . ' | null';
    }
}
