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

    public function getCityByProvince($provinceId)
    {
        $data = $this->indo::findProvince($provinceId, ['cities']);
        return $data->cities;
    }

    public function getDistrictByCity($cityId)
    {
        $data = $this->indo::findCity($cityId, ['districts']);
        return $data->districts;
    }

    public function getVillageByDistrict($districtId)
    {
        $data = $this->indo::findDistrict($districtId, ['villages']);
        return $data->villages;
    }
}
