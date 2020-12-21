<?php

namespace App\Repositories;

use App\Models\Fleet;
use Indonesia;
use Carbon\Carbon;

class FleetRepository
{
    protected $fleet;

    public function __construct(Fleet $fleet)
    {
        $this->fleet = $fleet;
    }

    /**
     * Get all fleet
     *
     * @param $data
     * @return Fleet
     */
    public function getAll()
    {
        return $this->fleet->get();
    }
}
