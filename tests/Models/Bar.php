<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    public $timestamps = false;

    protected $casts = [
        'today' => 'date',
        'yesterday' => 'datetime',
        'theme' => 'object',
    ];
}
