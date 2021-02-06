<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\RouteRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
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

    /**
     * Get route destination island
     *
     * @param array $data
     */
    public function getDestinationIslandService()
    {
        try {
            $result = $this->routeRepository->getDestinationIslandRepo();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * Create route service
     *
     * @param array $data
     */
    public function createRouteService($data = [])
    {
        $validator = Validator::make($data, [
            'origin' => [
                'bail','required','max:50',
            ],
            'destinationCity' => [
                'bail','required','max:50',
            ],
            'destinationDistrict' => [
                'bail','required','max:50',
            ],
            'destinationIsland' => 'bail|required|max:50',
            'fleet' => 'bail|required',
            'price' => 'bail|required',
            'minWeight' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // dd($data);

        DB::beginTransaction();
        try {
            $result = $this->routeRepository->createRouteRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     * edit route service
     *
     * @param array $data
     */
    public function editRouteService($data = [])
    {
        $validator = Validator::make($data, [
            'origin' => [
                'bail','required','max:50',
            ],
            'destinationCity' => [
                'bail','required','max:50',
            ],
            'destinationDistrict' => [
                'bail','required','max:50',
            ],
            'destinationIsland' => 'bail|required|max:50',
            'fleet' => 'bail|required',
            'price' => 'bail|required',
            'minWeight' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->routeRepository->editRouteRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     * delete route service
     *
     * @param array $data
     */
    public function deleteRouteService($data = [])
    {
        $validator = Validator::make($data, [
            'routeId' => 'bail|required|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->routeRepository->deleteRouteRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }
}
