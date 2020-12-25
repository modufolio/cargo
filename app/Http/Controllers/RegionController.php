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

    public function getProvince($provinceId)
    {
        try {
            $response = $this->regionService->getProvince($provinceId);
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

    public function getCity($cityId)
    {
        try {
            $response = $this->regionService->getCity($cityId);
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

    public function getDistrict($districtId)
    {
        try {
            $response = $this->regionService->getDistrict($districtId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getVillages($districtId)
    {
        try {
            $response = $this->regionService->getVillageByDistrict($districtId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getVillage($villageId)
    {
        try {
            $response = $this->regionService->getVillage($villageId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getRegions(Request $request)
    {
        $data = $request->only([
            'name',
            'regionType'
        ]);
        try {
            $response = $this->regionService->getRegionByName($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }

    public function getPaginateRegions(Request $request)
    {
        $data = $request->only([
            'name',
            'regionType'
        ]);
        try {
            $response = $this->regionService->getPaginateRegionByName($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $response);
    }
}
