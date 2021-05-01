<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    protected $casts = [
        'today' => 'date',
        'yesterday' => 'datetime',
        'theme' => 'object',
    ];
}
