<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Services;

use Exception;
use Illuminate\Support\Str;

class FormatNamespace
{
    /**
     * @param  string    $path
     * @throws Exception
     * @return string
     */
    public function get(string $path): string
    {
        $namespace = $this->fromFileContent($path);

        if (null === $namespace) {
            $namespace = $this->fromFilePath($path);
        }

        return $namespace;
    }

    /**
     * @param  string      $path
     * @return null|string
     */
    protected function fromFileContent(string $path): ?string
    {
        $namespace = null;

        if (file_exists($path)) {
            $handle = fopen($path, 'r');

            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'namespace') === 0) {
                        $parts = explode(' ', $line);
                        $namespace = rtrim(trim($parts[1]), ';');

                        break;
                    }
                }

                fclose($handle);
            }

            $fileName = Str::ucfirst(pathinfo($path, PATHINFO_FILENAME));
            $namespace .= '\\' . $fileName;
        }

        return $namespace;
    }

    /**
     * @param  string $path
     * @return string
     */
    protected function fromFilePath(string $path): string
    {
        if (!starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $namespace = str_replace(base_path(), '', $path);
        $namespace = preg_replace('/\//', '\\', $namespace);
        $namespace = str_replace('.php', '', $namespace ?? '');

        return Str::ucfirst(substr($namespace, 2));
    }
}
