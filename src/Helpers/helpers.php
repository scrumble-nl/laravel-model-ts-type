<?php

declare(strict_types=1);

if (!function_exists('unify_path')) {
    /**
     * Unify path name so it always matches
     *
     * @param  string $path
     * @return string
     */
    function unify_path(string $path): string
    {
        return preg_replace('/\\\\/', '/', $path);
    }
}

if (!function_exists('format_namespace')) {
    /**
     * Format the given path to a namespace
     *
     * @param  string $path
     * @return string
     */
    function format_namespace(string $path): string
    {
        if (!starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $namespace = str_replace(base_path(), '', $path);
        $namespace = preg_replace('/\//', '\\', $namespace);
        $namespace = str_replace('.php', '', $namespace);

        $namespace = Str::ucfirst(substr($namespace, 2));

        return $namespace;
    }
}
