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
    public function getByFleetOriginDestination($data)
    {
        $validator = Validator::make($data, [
            'origin'            => 'bail|required|max:50',
            'destination'       => 'bail|required|max:50',
            'fleetId'           => 'bail|required|integer'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->routeRepository->getByFleetOriginDestination($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        if (!$result) {
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }

        return $result;
    }
}
