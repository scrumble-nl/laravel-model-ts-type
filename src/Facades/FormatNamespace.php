<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Facades;

use Exception;
use Illuminate\Support\Facades\Facade;

class FormatNamespace extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'format-namespace';
    }
}
