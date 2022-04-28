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
     * @return string
     */
    public function format(): string
    {
        $name = $this->getName();
        $values = $this->getValues();

        return "type {$name} = {$values};\n";
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return kebab_case($this->getName());
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
     * @return string
     */
    private function getName(): string
    {
        $shortName = $this->reflectionEnum->getShortName();

        $firstLetter = $shortName[0];
        $secondLetter = $shortName[1];

        if ('E' === $firstLetter && strtoupper($secondLetter) === $secondLetter) {
            return substr($shortName, 1);
        }

        return $shortName;
    }
}
