<?php

namespace App\Repositories;

use App\Models\Base;
use Carbon\Carbon;
use InvalidArgumentException;

class BaseRepository
{
    protected $base;

    public function __construct(Base $base)
    {
        $this->base = $base;
    }

    /**
     * Get base by name
     *
     * @param array $data
     * @return Base
     */
    public function getBaseByNameRepo($data)
    {
        $data = $this->base->where('name', 'ilike', '%'.$data['value'].'%')->get();
        return $data;
    }
}
