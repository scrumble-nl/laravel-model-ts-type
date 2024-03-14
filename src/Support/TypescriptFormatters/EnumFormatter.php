<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\TypescriptFormatters;

use UnitEnum;
use ReflectionEnum;
use ReflectionException;
use ReflectionEnumPureCase;
use ReflectionEnumBackedCase;

class EnumFormatter
{
    /**
     * @var class-string<UnitEnum>
     */
    private string $fullyQualifiedName;

    /**
     * @var ReflectionEnum
     */
    private ReflectionEnum $reflectionEnum;

    /**
     * @param  class-string<UnitEnum>              $fullyQualifiedName
     * @throws ReflectionException
     */
    public function __construct(string $fullyQualifiedName)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->reflectionEnum = new ReflectionEnum($fullyQualifiedName);
    }

    /**
     * @throws ReflectionException
     * @return string
     */
    public function format(): string
    {
        $name = $this->getName();
        $values = $this->getValues();

        return "type {$name} = {$values};" . PHP_EOL;
    }

    /**
     * @throws ReflectionException
     * @return string
     */
    public function getFileName(): string
    {
        return extractEnumName($this->fullyQualifiedName);
    }

    /**
     * @return string
     */
    private function getValues(): string
    {
        $cases = $this->reflectionEnum->getCases();

        $values = array_map(function ($case) {
            /** @phpstan-ignore-next-line */
            $value = $case->getValue()->value;

            if (is_string($value)) {
                return "'{$value}'";
            }

            return $value;
        }, $cases);

        return implode(' | ', $values);
    }

    /**
     * @throws ReflectionException
     * @return string
     */
    private function getName(): string
    {
        return extractEnumShortName($this->fullyQualifiedName);
    }
}
