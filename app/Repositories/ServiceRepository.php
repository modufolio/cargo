<?php

namespace App\Repositories;

use App\Models\Service;
use Carbon\Carbon;

class ServiceRepository
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get all Service
     *
     * @return Service
     */
    public function getAll()
    {
        return $this->service->get();
    }
}
