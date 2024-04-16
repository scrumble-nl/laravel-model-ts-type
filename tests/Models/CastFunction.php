<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

class CastFunction extends Model
{
    public $timestamps = false;

    /**
     * @return string[]
     */
    protected function casts()
    {
        return [
            'foo_id' => 'integer',
            'bar_id' => 'integer',
        ];
    }
}
