<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\SenderController;
use App\Http\Controllers\ReceiverController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DebtorController;

Route::group(['middleware' => ['auth:api','auth.custom']], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('check-user', [AuthController::class, 'checkLogin']);
    Route::resource('role', RoleController::class);
    Route::resource('user', UserController::class);
    Route::resource('fleet', FleetController::class);
    Route::resource('pickup', PickupController::class);
    Route::resource('sender', SenderController::class);
    Route::resource('receiver', ReceiverController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('service', ServiceController::class);
    Route::resource('debtor', DebtorController::class);
    Route::get('get-provinces', [RegionController::class, 'getProvinces']);
    Route::get('province/{provinceId}', [RegionController::class, 'getProvince']);
    Route::get('get-cities/{provinceId}', [RegionController::class, 'getCities']);
    Route::get('city/{cityId}', [RegionController::class, 'getCity']);
    Route::get('get-districts/{cityId}', [RegionController::class, 'getDistricts']);
    Route::get('district/{districtId}', [RegionController::class, 'getDistrict']);
    Route::get('get-villages/{districtId}', [RegionController::class, 'getVillages']);
    Route::get('village/{villageId}', [RegionController::class, 'getVillage']);
    Route::post('get-regions', [RegionController::class, 'getRegions']);
    Route::post('get-regions-paginate', [RegionController::class, 'getPaginateRegions']);
    Route::prefix('bill')->group(function() {
        Route::post('calculate', [BillController::class, 'calculatePrice']);
    });
    Route::prefix('route')->group(function() {
        Route::post('get-fleet-origin-destination', [RouteController::class, 'getByFleetOriginDestination']);
    });
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

Route::middleware('guest')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('refresh-token', [AuthController::class, 'refreshToken'])->name('refreshToken');
    Route::resource('test', TestController::class);
});
