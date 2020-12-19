<?php
namespace App\Services;

use App\Repositories\RegionRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class RegionService {

    protected $regionRepository;

    public function __construct(RegionRepository $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    public function getAllProvince()
    {
        $result = $this->regionRepository->getAllProvince();
        return $result;
    }

    public function getCityByProvince($provinceId)
    {
        $result = $this->regionRepository->getCityByProvince($provinceId);
        return $result;
    }

    public function getDistrictByCity($cityId)
    {
        $result = $this->regionRepository->getDistrictByCity($cityId);
        return $result;
    }

    public function getVilageByDistrict($districtId)
    {
        $result = $this->regionRepository->getVillageByDistrict($districtId);
        return $result;
    }
}
