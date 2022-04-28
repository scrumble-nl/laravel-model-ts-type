<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\TypescriptFormatters;

use ReflectionEnum;
use ReflectionException;

class EnumFormatter
{
    /**
     * @var string
     */
    private string $enumPath;

    /**
     * @var string
     */
    private string $fullyQualifiedName;

    /**
     * @var ReflectionEnum
     */
    private ReflectionEnum $reflectionEnum;

    /**
     * @param  string              $enumPath
     * @param  string              $fullyQualifiedName
     * @throws ReflectionException
     */
    public function __construct(string $enumPath, string $fullyQualifiedName)
    {
        $this->enumPath = $enumPath;
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
