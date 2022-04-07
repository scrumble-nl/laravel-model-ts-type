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
        return preg_replace('/\\\\/', '/', $path);
    }
}
