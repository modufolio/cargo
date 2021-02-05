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

    public function getAllCityService()
    {
        $result = $this->regionRepository->getAllCityRepo();
        return $result;
    }

    public function getProvince($provinceId)
    {
        $result = $this->regionRepository->getProvince($provinceId);
        return $result;
    }

    public function getCityByProvince($provinceId)
    {
        $result = $this->regionRepository->getCityByProvince($provinceId);
        return $result;
    }

    public function getCity($cityId)
    {
        $result = $this->regionRepository->getCity($cityId);
        return $result;
    }

    public function getDistrictByCity($cityId)
    {
        $result = $this->regionRepository->getDistrictByCity($cityId);
        return $result;
    }

    public function getDistrict($districtId)
    {
        $result = $this->regionRepository->getDistrict($districtId);
        return $result;
    }

    public function getVillageByDistrict($districtId)
    {
        $result = $this->regionRepository->getVillageByDistrict($districtId);
        return $result;
    }

    public function getVillage($villageId)
    {
        $result = $this->regionRepository->getVillage($villageId);
        return $result;
    }

    public function getRegionByName($data)
    {
        $validator = Validator::make($data, [
            'name'          => 'bail|required|max:30',
            'regionType'    => 'bail|max:30',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $result = $this->regionRepository->getRegionByName($data);
        return $result;
    }

    public function getPaginateRegionByName($data)
    {
        $validator = Validator::make($data, [
            'name'          => 'bail|required|max:30',
            'regionType'    => 'bail|max:30',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $result = $this->regionRepository->getPaginateRegionByName($data);
        return $result;
    }
}
