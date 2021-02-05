<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\RouteRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class RouteService {

    protected $routeRepository;

    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * Get route by fleetId, origin, destination.
     *
     * @param Array $data
     * @return mixed
     */
    public function getByFleetOriginDestinationService($data)
    {
        $validator = Validator::make($data, [
            'origin'                    => 'bail|required|max:50',
            'destination_city'          => 'bail|required|max:50',
            'destination_district'     => 'bail|required|max:50',
            'fleetId'                   => 'bail|required|integer'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->routeRepository->getRouteRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        if (!$result) {
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }

        return $result;
    }

    /**
     * Get route by city of destination
     *
     * @param array $data
     * @return Route
     */
    public function getByCityService($data)
    {
        $validator = Validator::make($data, [
            'origin'                    => 'bail|required|max:50',
            'destination'               => 'bail|required|max:50',
            'fleetId'                   => 'bail|required|integer'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->routeRepository->getRouteByCityRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        if (!$result) {
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }

        return $result;
    }

    /**
     * Get all routes pagination
     */
    public function getAllPaginateService($data)
    {
        try {
            $result = $this->routeRepository->getAllPaginateRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * Get route service by city
     *
     * @param array $data
     */
    public function getRouteByCityService($data)
    {
        try {
            $result = $this->routeRepository->getAllPaginate($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }
}
