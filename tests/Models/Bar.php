<?php

declare(strict_types=1);

namespace Tests\Models;

use Tests\Models\Enums\ETestEnum;
use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    /**
     * @var string
     */
    protected $table = 'bar';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = [
        'today' => 'date',
        'yesterday' => 'datetime',
        'theme' => 'object',
        'test' => ETestEnum::class,
    ];
}
