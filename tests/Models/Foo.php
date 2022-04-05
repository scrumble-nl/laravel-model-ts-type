<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Foo extends Model
{
    protected $casts = [
        'total'   => 'int',
        'my_list' => 'array',
    ];

    public $timestamps = false;
}
