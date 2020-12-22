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

Route::group(['middleware' => ['auth:api','auth.custom']], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('check-user', [AuthController::class, 'checkLogin']);
    Route::resource('role', RoleController::class);
    Route::resource('user', UserController::class);
    Route::resource('fleet', FleetController::class);
    Route::resource('pickup', PickupController::class);
    Route::resource('sender', SenderController::class);
    Route::resource('receiver', ReceiverController::class);
    Route::get('get-provinces', [RegionController::class, 'getProvinces']);
    Route::get('get-cities/{provinceId}', [RegionController::class, 'getCities']);
    Route::get('get-districts/{cityId}', [RegionController::class, 'getDistricts']);
    Route::get('get-villages/{districtId}', [RegionController::class, 'getVillages']);
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

Route::middleware('guest')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('refresh-token', [AuthController::class, 'refreshToken'])->name('refreshToken');
    Route::resource('test', TestController::class);
});
