<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Support\Mutators;

class CastsConsts
{
    /**
     * @var array<int, string>
     */
    public const STRING_TYPES = ['string'];

    /**
     * @var array<int, string>
     */
    public const NUMBER_TYPES = ['integer', 'float', 'double', 'decimal'];

    /**
     * @var array<int, string>
     */
    public const BOOL_TYPES = ['boolean'];

    /**
     * @var array<int, string>
     */
    public const DATE_TYPES = ['date', 'datetime', 'timestamp'];

    /**
     * @var array<int, string>
     */
    public const ARRAY_TYPES = ['array', 'collection'];
}
