<?php
namespace App\Services;

// MODELS
use App\Models\User;

// REPO
use App\Repositories\BillRepository;
use App\Repositories\RouteRepository;

// OTHER
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class BillService {

    protected $billRepository;
    protected $routeRepository;

    public function __construct(BillRepository $billRepository, RouteRepository $routeRepository)
    {
        $this->billRepository = $billRepository;
        $this->routeRepository = $routeRepository;
    }

    /**
     * Get all bill.
     *
     * @return String
     */
    public function getAll()
    {
        return $this->billRepository->getAll();
    }

    /**
     * Calculate price.
     *
     * @param Array $data
     * @return mixed
     */
    public function calculatePrice($items = [], $route = [])
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Item tidak ditemukan');
        }
        if (empty($route)) {
            throw new InvalidArgumentException('Rute tidak masuk dalam jangkauan');
        }

        try {
            $result = $this->billRepository->calculatePrice($items, $route);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal memperkirakan biaya');
        }
        return $result;
    }

    /**
     * Calculate price.
     *
     * @param Array $data
     * @return mixed
     */
    public function calculatePriceWithoutPromo($items = [], $route = [])
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Item tidak ditemukan');
        }
        if (empty($route)) {
            throw new InvalidArgumentException('Rute tidak masuk dalam jangkauan');
        }

        try {
            $result = $this->billRepository->calculatePriceWithoutPromo($items, $route);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal memperkirakan biaya');
        }
        return $result;
    }
}
