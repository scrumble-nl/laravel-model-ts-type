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

if (!function_exists('format_namespace')) {
    /**
     * Format the given path to a namespace.
     *
     * @param  string    $path
     * @throws Exception
     * @return string
     */
    function format_namespace(string $path): string
    {
        $ns = null;

        if (file_exists($path)) {
            // Read namespace out of file
            $handle = fopen($path, 'r');

            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (0 === strpos($line, 'namespace')) {
                        $parts = explode(' ', $line);
                        $ns = rtrim(trim($parts[1]), ';');

                        break;
                    }
                }

                fclose($handle);
            }

            // Add class name after namespace
            $fileName = Str::ucfirst(pathinfo($path, PATHINFO_FILENAME));
            $ns .= '\\' . $fileName;

            return $ns;
        }

        // Old way to generate a PSR-2 namespace
        if (null === $ns) {
            if (!starts_with($path, '/')) {
                $path = '/' . $path;
            }

            $namespace = str_replace(base_path(), '', $path);
            $namespace = preg_replace('/\//', '\\', $namespace);
            $namespace = str_replace('.php', '', $namespace);

            return Str::ucfirst(substr($namespace, 2));
        }

        throw new Exception('Could not make a valid namespace path', 500);
    }
}
