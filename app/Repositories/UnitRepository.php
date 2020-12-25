<?php

namespace App\Repositories;

use App\Models\Unit;
use Carbon\Carbon;

class UnitRepository
{
    protected $unit;

    public function __construct(Unit $unit)
    {
        $this->unit = $unit;
    }

    /**
     * Get all unit
     *
     * @return Unit
     */
    public function getAll()
    {
        return $this->unit->get();
    }
}
