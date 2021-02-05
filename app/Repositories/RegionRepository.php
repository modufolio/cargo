<?php

namespace App\Repositories;

use Indonesia;

class RegionRepository
{
    protected $indo;

    public function __construct(Indonesia $indo)
    {
        $this->indo = $indo;
    }

    public function getAllProvince()
    {
        $data = $this->indo::allProvinces();
        return $data;
    }

    public function getAllCityRepo()
    {
        $data = $this->indo::allCities();
        return $data;
    }

    public function getProvince($provinceId)
    {
        $data = $this->indo::findProvince($provinceId);
        return $data;
    }

    public function getCityByProvince($provinceId)
    {
        $data = $this->indo::findProvince($provinceId, ['cities']);
        return $data->cities;
    }

    public function getCity($cityId)
    {
        $data = $this->indo::findCity($cityId);
        return $data;
    }

    public function getDistrictByCity($cityId)
    {
        $data = $this->indo::findCity($cityId, ['districts']);
        return $data->districts;
    }

    public function getDistrict($districtId)
    {
        $data = $this->indo::findDistrict($districtId);
        return $data;
    }

    public function getVillageByDistrict($districtId)
    {
        $data = $this->indo::findDistrict($districtId, ['villages']);
        return $data->villages;
    }

    public function getVillage($villageId)
    {
        $data = $this->indo::findVillage($villageId);
        return $data;
    }

    public function getRegionByName($data)
    {
        switch ($data['regionType']) {
            case 'city':
                $result = $this->indo::search($data['name'])->allCities();
                break;
            case 'province':
                $result = $this->indo::search($data['name'])->allProvinces();
                break;
            case 'village':
                $result = $this->indo::search($data['name'])->allVillages();
                break;
            case 'district':
                $result = $this->indo::search($data['name'])->allDistricts();
                break;
            default:
                $result = $this->indo::search($data['name'])->all();
                break;
        }
        return $result;
    }

    public function getPaginateRegionByName($data)
    {
        switch ($data['regionType']) {
            case 'city':
                $result = $this->indo::search($data['name'])->paginateCities();
                break;
            case 'province':
                $result = $this->indo::search($data['name'])->paginateProvinces();
                break;
            case 'village':
                $result = $this->indo::search($data['name'])->paginateVillages();
                break;
            case 'district':
                $result = $this->indo::search($data['name'])->paginateDistricts();
                break;
            default:
                $result = $this->indo::search($data['name'])->all();
                break;
        }
        return $result;
    }
}
