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
use App\Http\Controllers\PromoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PickupPlanController;
use App\Http\Controllers\ProofOfPickupController;
use App\Http\Controllers\ItemController;

// All Authenticated User
Route::group(['middleware' => ['auth:api','auth.custom']], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('check-user', [AuthController::class, 'checkLogin']);

    // Role
    Route::get('role', [RoleController::class, 'index']);
    Route::prefix('role')->group(function() {
        Route::get('by-id/{id}', [RoleController::class, 'show']);
    });

    // Fleet
    Route::get('fleet', [FleetController::class, 'index']);

    // Pickup
    Route::post('pickup', [PickupController::class, 'store']);
    Route::prefix('pickup')->group(function() {
        Route::post('list', [PickupController::class, 'listPickupCustomer']);
    });

    // Sender
    Route::resource('sender', SenderController::class);

    // Receiver
    Route::resource('receiver', ReceiverController::class);

    // Unit
    Route::resource('unit', UnitController::class);

    // Service
    Route::resource('service', ServiceController::class);

    // Debtor
    Route::resource('debtor', DebtorController::class);

    // Address
    Route::get('get-provinces', [RegionController::class, 'getProvinces']);
    Route::get('province/{provinceId}', [RegionController::class, 'getProvince']);
    Route::get('get-cities/{provinceId}', [RegionController::class, 'getCities']);
    Route::get('city/{cityId}', [RegionController::class, 'getCity']);
    Route::get('get-all-cities', [RegionController::class, 'getAllCities']);
    Route::get('get-districts/{cityId}', [RegionController::class, 'getDistricts']);
    Route::get('district/{districtId}', [RegionController::class, 'getDistrict']);
    Route::get('get-villages/{districtId}', [RegionController::class, 'getVillages']);
    Route::get('village/{villageId}', [RegionController::class, 'getVillage']);
    Route::post('get-regions', [RegionController::class, 'getRegions']);
    Route::post('get-regions-paginate', [RegionController::class, 'getPaginateRegions']);

    // Bill
    Route::prefix('bill')->group(function() {
        Route::post('calculate', [BillController::class, 'calculatePrice']);
    });

    // Route
    Route::prefix('route')->group(function() {
        Route::post('get-fleet-origin-destination', [RouteController::class, 'getByFleetOriginDestination']);
    });

    // Promo
    Route::prefix('promo')->group(function() {
        Route::get('user', [PromoController::class, 'getPromoUser']);
        Route::get('creator', [PromoController::class, 'getCreatorPromo']);
        Route::post('select', [PromoController::class, 'selectPromo']);
    });

    // User
    Route::get('user-by-id', [UserController::class, 'show']);
    Route::prefix('user')->group(function() {
        Route::post('update-profile', [UserController::class, 'updateProfile']);
        Route::post('upload-avatar', [UserController::class, 'uploadAvatar']);
        Route::post('remove-avatar', [UserController::class, 'removeAvatar']);
    });

    // Driver Only
    Route::group(['middleware' => ['driver.panel']], function () {
        Route::prefix('driver')->group(function() {
            // pickup plan
            Route::prefix('pickup-plan')->group(function() {
                Route::post('list', [PickupPlanController::class, 'getDriverPickupPlanList']);
            });

            // Pickup
            Route::prefix('pickup')->group(function() {
                Route::post('get-by-pickup-plan', [PickupController::class, 'getByPickupPlanDriver']);
                Route::post('total-volume-kilo', [PickupController::class, 'getTotalVolumeAndKiloPickup']);
                Route::post('detail', [PickupController::class, 'getDetailPickup']);
            });

            // proof of pickup
            Route::prefix('pop')->group(function() {
                Route::post('create', [ProofOfPickupController::class, 'createPOP']);
            });

            // item
            Route::prefix('item')->group(function() {
                Route::post('update', [ItemController::class, 'update']);
            });
        });
    });

    // Admin Only
    Route::group(['middleware' => ['admin.panel']], function () {
        // User
        Route::get('user', [UserController::class, 'index']);
        Route::post('user-paginate', [UserController::class, 'paginate']);
        Route::post('user', [UserController::class, 'store']);
        Route::prefix('user')->group(function() {
            Route::post('delete', [UserController::class, 'destroy']);
            Route::post('update', [UserController::class, 'update']);
            Route::post('create', [UserController::class, 'store']);
            Route::post('search-name', [UserController::class, 'searchName']);
            Route::post('search-email', [UserController::class, 'searchEmail']);
            Route::post('change-password', [UserController::class, 'changePassword']);
        });

        // Role
        Route::prefix('role')->group(function() {
            Route::get('list-feature', [RoleController::class, 'featureList']);
            Route::post('paginate', [RoleController::class, 'paginate']);
            Route::post('update', [RoleController::class, 'update']);
            Route::post('create', [RoleController::class, 'store']);
            Route::post('delete', [RoleController::class, 'destroy']);
        });

        // Menu
        Route::get('menu', [MenuController::class, 'index']);

        // Route
        Route::prefix('route')->group(function() {
            Route::post('paginate', [RouteController::class, 'paginate']);
            Route::post('create', [RouteController::class, 'create']);
            Route::post('delete', [RouteController::class, 'delete']);
            Route::post('edit', [RouteController::class, 'edit']);
            Route::get('island', [RouteController::class, 'listIsland']);
        });

        // Promo
        Route::prefix('promo')->group(function() {
            Route::post('paginate', [PromoController::class, 'paginate']);
            Route::post('create', [PromoController::class, 'create']);
            Route::post('delete', [PromoController::class, 'delete']);
            Route::post('update', [PromoController::class, 'update']);
        });

        // Vehicle
        Route::prefix('vehicle')->group(function() {
            Route::post('search', [VehicleController::class, 'search']);
            Route::post('edit', [VehicleController::class, 'edit']);
            Route::post('create', [VehicleController::class, 'create']);
            Route::post('delete', [VehicleController::class, 'delete']);
            Route::post('paginate', [VehicleController::class, 'paginate']);
        });

        // Branch
        Route::prefix('branch')->group(function() {
            Route::post('paginate', [BranchController::class, 'paginate']);
            Route::post('create', [BranchController::class, 'create']);
            Route::post('delete', [BranchController::class, 'delete']);
            Route::post('update', [BranchController::class, 'update']);
            Route::get('list', [BranchController::class, 'list']);
        });

        // Driver
        Route::prefix('driver')->group(function() {
            Route::post('search', [DriverController::class, 'search']);
            Route::post('paginate', [DriverController::class, 'paginate']);
            Route::post('edit', [DriverController::class, 'edit']);
            Route::post('disable', [DriverController::class, 'disable']);
            Route::post('create', [DriverController::class, 'create']);
        });

        // Pickup Plan
        Route::prefix('pickup-plan')->group(function() {
            Route::post('save', [PickupPlanController::class, 'save']);
            Route::post('get-pickup', [PickupPlanController::class, 'getPaginatePickup']);
            Route::post('delete', [PickupPlanController::class, 'delete']);
            Route::post('list', [PickupPlanController::class, 'getList']);
            Route::post('delete-po', [PickupPlanController::class, 'deletePickupOrder']);
            Route::post('add-po', [PickupPlanController::class, 'addPickupOrder']);
        });

        // Pickup
        Route::prefix('pickup')->group(function() {
            Route::post('paginate', [PickupController::class, 'paginate']);
            Route::post('get-by-pickup-plan', [PickupController::class, 'getByPickupPlan']);
            Route::post('outstanding', [PickupController::class, 'getOutstanding']);
        });

        // Proof of pickup
        Route::prefix('pop')->group(function() {
            Route::post('create', [ProofOfPickupController::class, 'createPOP']);
        });

         // Fleet
         Route::prefix('fleet')->group(function() {
            Route::post('list', [FleetController::class, 'list']);
        });
    });

});

// Guest / All User
Route::middleware('guest')->group(function () {
    // Auth
    Route::prefix('google')->group(function() {
        Route::post('login', [GoogleController::class, 'loginGoogle']);
        Route::post('register', [GoogleController::class, 'registerGoogle']);
    });
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('login-web', [AuthController::class, 'loginWeb'])->name('loginWeb');
    Route::post('login-driver', [AuthController::class, 'loginDriver'])->name('loginDriver');
    Route::post('refresh-token', [AuthController::class, 'refreshToken'])->name('refreshToken');
    Route::prefix('user')->group(function() {
        Route::post('forgot-password', [UserController::class, 'forgotPassword']);
    });



    // Test
    Route::resource('test', TestController::class);
});
