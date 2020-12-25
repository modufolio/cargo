<?php

namespace App\Repositories;

use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;

class UnitRepository
{
    protected $unit;

    public function __construct(Unit $unit)
    {
        $this->unit = $unit;
    }
}
