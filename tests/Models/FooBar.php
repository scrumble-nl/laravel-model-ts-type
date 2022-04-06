<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FooBar extends Pivot
{
    public $timestamps = false;

    protected $casts = [
        'foo_id' => 'int',
        'bar_id' => 'int',
    ];
}