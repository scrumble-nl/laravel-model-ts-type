<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Mutators;

class CastsConsts
{
    /**
     * @var array
     */
    public const STRING_TYPES = ['string'];

    /**
     * @var array
     */
    public const NUMBER_TYPES = ['integer', 'float', 'double', 'decimal'];

    /**
     * @var array
     */
    public const BOOL_TYPES = ['boolean'];

    /**
     * @var array
     */
    public const DATE_TYPES = ['date', 'datetime', 'timestamp'];

    /**
     * @var array
     */
    public const ARRAY_TYPES = ['array', 'collection'];
}
