<?php

namespace App\Repositories;

// MODELS
use App\Models\Route;

// OTHER
use InvalidArgumentException;
use Carbon\Carbon;

class RouteRepository
{
    protected $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Get route by fleet / armada,
     * origin, destination city, and destination district
     *
     * @param array $data
     * @return Route
     */
    public function getRouteRepo($data)
    {
        $route = $this->route->where([
            ['fleet_id', '=', $data['fleetId']],
            ['origin', '=', $data['origin']],
            ['destination_district', '=', $data['destination_district']],
            ['destination_city', '=', $data['destination_city']],
        ])->first();
        return $route;
    }

    /**
     * Get all route paginate
     *
     * @param $pickupId
     * @return mixed
     */
    public function getAllPaginate($data = [])
    {
        $origin = $data['origin'];
        $perPage = $data['perPage'];
        $destination = $data['destination'];

        if (empty($perPage)) {
            $perPage = 15;
        }

        if (!empty($origin)) {
            $route = $this->route->sortable()->where('origin', 'like', '%'.$origin.'%')->simplePaginate($perPage);
        }

        if (!empty($destination)) {
            $route = $this->route->sortable()->where('destination_district', 'like', '%'.$destination.'%')->simplePaginate($perPage);
        }

        // $route = $this->route->sortable(['created_at' => 'desc'])->simplePaginate($perPage);
        return $route;
    }

    /**
     * Get route by fleet / armada,
     * origin, and destination city
     *
     * @param array $data
     * @return Route
     */
    public function getRouteByCityRepo($data)
    {
        $route = $this->route->where([
            ['fleet_id', '=', $data['fleetId']],
            ['origin', '=', $data['origin']],
            ['destination_city', '=', $data['destination']],
        ])->first();
        return $route;
    }
}
