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
use App\Http\Controllers\ShipmentPlanController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\TransitController;
use App\Http\Controllers\ProofOfDeliveryController;
use App\Http\Controllers\DropController;
use App\Http\Controllers\AddressController;


// All Authenticated User
Route::group(['middleware' => ['auth:api','auth.custom','cors.custom']], function () {
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
    Route::post('sender', [SenderController::class, 'store']);
    Route::get('sender', [SenderController::class, 'index']);
    Route::put('sender/{id}', [SenderController::class, 'update']);
    Route::delete('sender/{id}', [SenderController::class, 'destroy']);
    Route::get('sender/primary', [SenderController::class, 'getPrimary']);

    // Receiver
    Route::post('receiver', [ReceiverController::class, 'store']);
    Route::get('receiver', [ReceiverController::class, 'index']);
    Route::put('receiver/{id}', [ReceiverController::class, 'update']);
    Route::delete('receiver/{id}', [ReceiverController::class, 'destroy']);

    // Address
    Route::post('address/search', [AddressController::class, 'search']);

    // Unit
    Route::resource('unit', UnitController::class);

    // Service
    Route::get('service', [ServiceController::class, 'index']);
    Route::post('service/paginate', [ServiceController::class, 'getPaginate']);
    Route::post('service/create', [ServiceController::class, 'create']);
    Route::post('service/update', [ServiceController::class, 'update']);

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

    // Tracking
    Route::prefix('tracking')->group(function() {
        Route::post('upload-picture', [TrackingController::class, 'uploadPicture']);
        Route::post('save', [TrackingController::class, 'store']);
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

            // shipment plan
            Route::prefix('shipment-plan')->group(function() {
                Route::get('list', [ShipmentPlanController::class, 'getDriverShipmentPlanList']);
                Route::post('pickup', [ShipmentPlanController::class, 'getPickupOrderDriverShipmentPlanList']);
                Route::post('dashboard', [ShipmentPlanController::class, 'getDashboardDriver']);
            });

            // proof of delivery
            Route::prefix('pod')->group(function() {
                Route::get('submit', [ProofOfDeliveryController::class, 'submitDriver']);
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
            Route::post('get-by-pickup-name-phone', [UserController::class, 'getByPickupNamePhone']);
            Route::post('get-default-by-pickup-name-phone', [UserController::class, 'getDefaultByPickupNamePhone']);
            Route::post('get-default-by-name-phone', [UserController::class, 'getDefaultByNamePhone']);
            Route::post('search-by-name-phone', [UserController::class, 'getByNamePhone']);
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
            Route::get('get-ten-vehicle', [VehicleController::class, 'getTenVehicle']);
        });

        // Branch
        Route::prefix('branch')->group(function() {
            Route::post('paginate', [BranchController::class, 'paginate']);
            Route::post('create', [BranchController::class, 'create']);
            Route::post('delete', [BranchController::class, 'delete']);
            Route::post('update', [BranchController::class, 'update']);
            Route::get('list', [BranchController::class, 'list']);
            Route::get('get-default-list', [BranchController::class, 'getDefaultList']);
        });

        // Driver
        Route::prefix('driver')->group(function() {
            Route::post('search', [DriverController::class, 'search']);
            Route::post('paginate', [DriverController::class, 'paginate']);
            Route::post('edit', [DriverController::class, 'edit']);
            Route::post('disable', [DriverController::class, 'disable']);
            Route::post('create', [DriverController::class, 'create']);
            Route::post('get-default-list', [DriverController::class, 'getDefaultList']);
        });

        // Pickup Plan
        Route::prefix('pickup-plan')->group(function() {
            Route::post('save', [PickupPlanController::class, 'save']);
            Route::post('get-pickup', [PickupPlanController::class, 'getPaginatePickup']);
            Route::post('delete', [PickupPlanController::class, 'delete']);
            Route::post('cancel', [PickupPlanController::class, 'cancel']);
            Route::post('list', [PickupPlanController::class, 'getList']);
            Route::post('delete-po', [PickupPlanController::class, 'deletePickupOrder']);
            Route::post('add-po', [PickupPlanController::class, 'addPickupOrder']);
        });

        // Pickup
        Route::prefix('pickup')->group(function() {
            Route::post('paginate', [PickupController::class, 'paginate']);
            Route::post('get-by-pickup-plan', [PickupController::class, 'getByPickupPlan']);
            Route::post('get-by-shipment-plan', [PickupController::class, 'getByShipmentPlan']);
            Route::post('create-pickup-admin', [PickupController::class, 'createPickupAdmin']);
            Route::post('delete', [PickupController::class, 'deletePickup']);
        });

        // Proof of pickup
        Route::prefix('pop')->group(function() {
            Route::post('create', [ProofOfPickupController::class, 'createPOP']);
            Route::post('outstanding', [ProofOfPickupController::class, 'getOutstanding']);
            Route::post('submitted', [ProofOfPickupController::class, 'getSubmitted']);
            Route::get('get-pending-draft', [ProofOfPickupController::class, 'getPendingAndDraft']);
            Route::post('detail-pickup', [ProofOfPickupController::class, 'getDetailPickup']);
            Route::post('update', [ProofOfPickupController::class, 'updatePOP']);
        });

         // Fleet
         Route::prefix('fleet')->group(function() {
            Route::post('list', [FleetController::class, 'list']);
        });

        // item
        Route::prefix('item')->group(function() {
            Route::post('fetch-by-pickup-id', [ItemController::class, 'getByPickup']);
            Route::post('update', [ItemController::class, 'update']);
        });

        // Shipment Plan
        Route::prefix('shipment-plan')->group(function() {
            Route::post('save', [ShipmentPlanController::class, 'save']);
            Route::post('get-pickup', [ShipmentPlanController::class, 'getPaginatePickup']);
            Route::post('delete', [PickupPlanController::class, 'delete']);
            Route::post('cancel', [ShipmentPlanController::class, 'cancel']);
            Route::post('list', [ShipmentPlanController::class, 'getList']);
            Route::post('delete-po', [ShipmentPlanController::class, 'deletePickupOrder']);
            Route::post('add-po', [ShipmentPlanController::class, 'addPickupOrder']);
        });

        // Transit pickup
        Route::prefix('transit')->group(function() {
            Route::post('create', [TransitController::class, 'draftTransit']);
            Route::post('outstanding', [TransitController::class, 'getOutstanding']);
            Route::post('submitted', [TransitController::class, 'getSubmitted']);
            Route::get('get-pending-draft', [TransitController::class, 'getPendingAndDraft']);
            Route::post('detail-pickup', [ProofOfPickupController::class, 'getDetailPickup']);
            Route::post('update', [TransitController::class, 'updateTransit']);
        });

        // Proof of delivery
        Route::prefix('pod')->group(function() {
            Route::post('create', [ProofOfPickupController::class, 'createPOD']);
            Route::post('outstanding', [ProofOfDeliveryController::class, 'getOutstanding']);
            Route::post('submitted', [ProofOfDeliveryController::class, 'getSubmitted']);
            Route::get('get-pending-draft', [ProofOfDeliveryController::class, 'getPendingAndDraft']);
            Route::post('detail-pickup', [ProofOfDeliveryController::class, 'getDetailPickup']);
            Route::post('update-status-delivery', [ProofOfDeliveryController::class, 'updateStatusDeliveryPOD']);
            Route::post('redelivery', [ProofOfDeliveryController::class, 'redeliveryPOD']);
        });

        // Drop
        Route::prefix('drop')->group(function() {
            Route::post('paginate', [DropController::class, 'paginate']);
            Route::post('get-by-pickup-plan', [DropController::class, 'getByPickupPlan']);
            Route::post('get-by-shipment-plan', [DropController::class, 'getByShipmentPlan']);
            Route::post('create', [DropController::class, 'create']);
            Route::post('delete', [DropController::class, 'deletePickup']);
        });
    });

});

Route::middleware(['auth:api','auth.custom','admin.panel','import.cors'])->group(function () {
    Route::prefix('route')->group(function() {
        Route::get('export', [RouteController::class, 'exportRoute']);
        Route::post('import', [RouteController::class, 'importRoute']);
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
    Route::post('test', [TestController::class, 'create']);
    Route::get('test', [TestController::class, 'index']);
    Route::post('test/update-pod', [TestController::class, 'update']);

    // Tracking
    Route::prefix('tracking')->group(function() {
        Route::post('get', [TrackingController::class, 'index']);
    });
});
