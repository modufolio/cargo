<?php

namespace App\Repositories;

// MODELS
use App\Models\Route;

// OTHER
use InvalidArgumentException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RouteImport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Validators\ValidationException;

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
    public function getAllPaginateRepo($data = [])
    {
        $origin = $data['origin'];
        $sort = $data['sort'];
        $perPage = $data['perPage'];
        $destinationCity = $data['destinationCity'];
        $destinationDistrict = $data['destinationDistrict'];
        $price = $data['price'];
        $minWeight = $data['minWeight'];
        $fleet = $data['fleet'];

        $route = $this->route->with('fleet')->sortable();

        if (empty($perPage)) {
            $perPage = 15;
        }

        if (!empty($sort['field'])) {
            $order = $sort['order'];
            if ($order == 'ascend') {
                $order = 'asc';
            } else if ($order == 'descend') {
                $order = 'desc';
            } else {
                $order = 'desc';
            }
            switch ($sort['field']) {
                case 'fleet.type':
                    $route = $route->sortable([
                        'fleet.type' => $order
                    ]);
                    break;
                case 'origin':
                    $route = $route->sortable([
                        'origin' => $order
                    ]);
                    break;
                case 'destination_city':
                    $route = $route->sortable([
                        'destination_city' => $order
                    ]);
                    break;
                case 'destination_district':
                    $route = $route->sortable([
                        'destination_district' => $order
                    ]);
                    break;
                case 'min_weight':
                    $route = $route->sortable([
                        'min_weight' => $order
                    ]);
                    break;
                case 'price':
                    $route = $route->sortable([
                        'price' => $order
                    ]);
                    break;
                default:
                    $route = $route->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($origin)) {
            $route = $route->where('origin', 'ilike', '%'.$origin.'%');
        }

        if (!empty($destinationDistrict)) {
            $route = $route->where('destination_district', 'ilike', '%'.$destinationDistrict.'%');
        }

        if (!empty($destinationCity)) {
            $route = $route->where('destination_city', 'ilike', '%'.$destinationCity.'%');
        }

        if (!empty($minWeight)) {
            $route = $route->where('min_weight', 'like', '%'.$minWeight.'%');
        }

        if (!empty($price)) {
            $route = $route->where('price', 'like', '%'.$price.'%');
        }

        if (!empty($fleet)) {
            $route = $route->whereHas('fleet', function($q) use ($fleet) {
                $q->where('type', 'ilike', '%'.$fleet.'%');
            });
        }

        $route = $route->paginate($perPage);

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

    /**
     * Get route destination island,
     *
     * @return Route
     */
    public function getDestinationIslandRepo()
    {
        $island = $this->route->select('destination_island')->get();
        $route = [];
        foreach ($island as $key => $value) {
            if (!in_array($value, $route)) {
                $route[] = $value;
            }
        }
        return $route;
    }

    /**
     * create route,
     *
     * @param array $data
     * @return Route
     */
    public function createRouteRepo($data = [])
    {
        $route = $this->route->where('origin', $data['origin'])
                ->where('destination_city', $data['destinationCity'])
                ->where('destination_district', $data['destinationDistrict'])
                ->where('fleet_id', $data['fleet'])->first();

        if ($route) {
            throw new InvalidArgumentException('rute asal sampai tujuan dengan armada yang ini sudah ada');
        }

        $route = new $this->route;
        $route->fleet_id = $data['fleet'];
        $route->origin = $data['origin'];
        $route->destination_island = $data['destinationIsland'];
        $route->destination_city = $data['destinationCity'];
        $route->destination_district = $data['destinationDistrict'];
        $route->price = $data['price'];
        $route->price_car = $data['priceCar'];
        $route->price_motorcycle = $data['priceMotorcycle'];
        $route->minimum_weight = $data['minWeight'];
        $route->save();
        return $route;
    }

    /**
     * edit route,
     *
     * @param array $data
     * @return Route
     */
    public function editRouteRepo($data = [])
    {
        $route = $this->route->where('id', '!=', $data['id'])->where('origin', $data['origin'])
                ->where('destination_city', $data['destinationCity'])
                ->where('destination_district', $data['destinationDistrict'])
                ->where('fleet_id', $data['fleet'])->first();

        if ($route) {
            throw new InvalidArgumentException('rute asal sampai tujuan dengan armada yang ini sudah ada');
        }

        $route = $this->route->find($data['id']);
        if (!$route) {
            throw new InvalidArgumentException('Rute tidak ditemukan');
        }
        $route->fleet_id = $data['fleet'];
        $route->origin = $data['origin'];
        $route->destination_island = $data['destinationIsland'];
        $route->destination_city = $data['destinationCity'];
        $route->destination_district = $data['destinationDistrict'];
        $route->price = $data['price'];
        $route->price_car = $data['priceCar'];
        $route->price_motorcycle = $data['priceMotorcycle'];
        $route->minimum_weight = $data['minWeight'];
        $route->save();
        return $route;
    }

    /**
     * Delete route repository
     *
     * @param array $data
     */
    public function deleteRouteRepo($data = [])
    {
        $route = $this->route->find($data['routeId']);
        if (!$route) {
            throw new InvalidArgumentException('Rute tidak ditemukan');
        }
        $route->delete();
        return $route;
    }

    /**
     * import data rute
     * @param Request $request
     */
    public function importRouteRepo($request)
    {
        try {
            $route = $request->file('route');
            Excel::import(new RouteImport, $route);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $row = 'pada baris '.$failure->row(); // row that went wrong
                // $attr = 'error pada attribut '.$failure->attribute(); // either heading key (if using heading row concern) or column index
                $msg = $failure->errors()[0]; // Actual error messages from Laravel validator
                // $val = 'nilai yang salah adalah '.$failure->values(); // The values of the row that has failed.
                $errorMsg = $msg.", ".$row;
                throw new InvalidArgumentException($errorMsg);
                break;
            }
        }
    }
}
