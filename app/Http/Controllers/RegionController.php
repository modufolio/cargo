<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RegionService;
use Exception;

class RegionController extends BaseController
{
    protected $regionService;

    public function __construct(RegionService $regionService)
    {
        $this->regionService = $regionService;
    }

    public function getProvinces()
    {
        try {
            $response = $this->regionService->getAllProvince();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getCities($provinceId)
    {
        try {
            $response = $this->regionService->getCityByProvince($provinceId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getDistricts($cityId)
    {
        try {
            $response = $this->regionService->getDistrictByCity($cityId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getVillages($districtId)
    {
        try {
            $response = $this->regionService->getVilageByDistrict($districtId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }
}
