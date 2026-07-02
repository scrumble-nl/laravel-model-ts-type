<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Foo extends Model
{
    public $timestamps = false;
    protected $table = 'foo';

    protected $casts = [
        'total' => 'int',
        'my_list' => 'array',
    ];
}
