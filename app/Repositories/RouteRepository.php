<?php

namespace App\Repositories;

// MODELS
use App\Models\Bill;
use App\Models\Pickup;
use App\Models\Route;
use App\Models\Fleet;
use App\Models\Item;

// OTHER
use InvalidArgumentException;
use Carbon\Carbon;

class RouteRepository
{
    protected $route;
    protected $fleet;

    public function __construct(Route $route, Fleet $fleet)
    {
        $this->route = $route;
        $this->fleet = $fleet;
    }

    /**
     * Get bill by pickup id
     *
     * @param $pickupId
     * @return mixed
     */
    public function getByFleetOriginDestination($data)
    {
        $route = $this->route->where([
            ['fleet_id', '=', $data['fleetId']],
            ['origin', '=', $data['origin']],
            ['destination', '=', $data['destination']]
        ])->first();
        return $route;
    }

    /**
     * Calculate price
     *
     * @param $unitTotal
     * @return mixed
     */
    public function calculatePrice($data)
    {
        $origin = $data['origin'];
        $destination = $data['destination'];
        $items = $data['items'];
        foreach ($items as $key => $value) {
            if ($value->unit_total >= $route->price) {
                // $data = $this->items->find($value->id);
                $finalPrice[$key] = $value->unit_total * $route->price;
                // $data->save();
            } else  {
                $finalPrice[$key] = 0;
            }
        }
        return $finalPrice;
    }
}
