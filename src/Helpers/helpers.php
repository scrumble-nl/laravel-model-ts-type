<?php

declare(strict_types=1);

if (!function_exists('unify_path')) {
    /**
     * Unify path name so it always matches.
     *
     * @param  string $path
     * @return string
     */
    function unify_path(string $path): string
    {
        return preg_replace('/\\\\/', '/', $path) ?? '';
    }
}

if (!function_exists('extractEnumName')) {
    /**
     * @param  class-string              $fullyQualifiedName
     * @throws ReflectionException
     * @return string
     */
    function extractEnumName(string $fullyQualifiedName): string
    {
        return kebab_case(extractEnumShortName($fullyQualifiedName));
    }
}

if (!function_exists('extractEnumShortName')) {
    /**
     * @param  class-string              $fullyQualifiedName
     * @throws ReflectionException
     * @return string
     */
    function extractEnumShortName(string $fullyQualifiedName): string
    {
        $reflectionEnum = new ReflectionEnum($fullyQualifiedName);
        $shortName = $reflectionEnum->getShortName();

        $firstLetter = $shortName[0];
        $secondLetter = $shortName[1];

        if ('E' === $firstLetter && strtoupper($secondLetter) === $secondLetter) {
            return substr($shortName, 1);
        }

        return $shortName;
    }
}
