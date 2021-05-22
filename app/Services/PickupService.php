<?php
namespace App\Services;

// use App\Models\Pickup;
use App\Repositories\PickupRepository;
use App\Repositories\ItemRepository;
use App\Repositories\BillRepository;
use App\Repositories\PromoRepository;
use App\Repositories\RouteRepository;
use App\Repositories\AddressRepository;
use App\Repositories\ProofOfPickupRepository;
use App\Repositories\DebtorRepository;
use App\Repositories\ReceiverRepository;
use App\Repositories\SenderRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\UserRepository;
use App\Repositories\PickupPlanRepository;
use App\Repositories\CostRepository;
use App\Repositories\RoleRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class PickupService {

	protected $pickupRepository;
	protected $itemRepository;
	protected $billRepository;
	protected $promoRepository;
	protected $routeRepository;
	protected $addressRepository;
	protected $senderRepository;
	protected $receiverRepository;
	protected $debtorRepository;
	protected $proofOfPickupRepository;
	protected $trackingRepository;
	protected $userRepository;
	protected $pickupPlanRepository;
	protected $costRepository;
	protected $roleRepository;

	public function __construct(
		PickupRepository $pickupRepository,
		ItemRepository $itemRepository,
		BillRepository $billRepository,
		PromoRepository $promoRepository,
		RouteRepository $routeRepository,
		AddressRepository $addressRepository,
		ProofOfPickupRepository $proofOfPickupRepository,
		SenderRepository $senderRepository,
		ReceiverRepository $receiverRepository,
		DebtorRepository $debtorRepository,
		TrackingRepository $trackingRepository,
		UserRepository $userRepository,
		PickupPlanRepository $pickupPlanRepository,
		CostRepository $costRepository,
		RoleRepository $roleRepository
	)
	{
		$this->pickupRepository = $pickupRepository;
		$this->itemRepository = $itemRepository;
		$this->billRepository = $billRepository;
		$this->promoRepository = $promoRepository;
		$this->routeRepository = $routeRepository;
		$this->addressRepository = $addressRepository;
		$this->popRepository = $proofOfPickupRepository;
		$this->senderRepository = $senderRepository;
		$this->receiverRepository = $receiverRepository;
		$this->debtorRepository = $debtorRepository;
		$this->trackingRepository = $trackingRepository;
		$this->userRepository = $userRepository;
		$this->pickupPlanRepository = $pickupPlanRepository;
		$this->costRepository = $costRepository;
		$this->roleRepository = $roleRepository;
	}

	/**
	 * Validate pickup data.
	 * Store to DB if there are no errors.
	 * @param array $data
	 */
	public function createPickupService($data)
	{
		$validator = Validator::make($data, [
			'fleetId'               => 'bail|required|max:19',
			'userId'                => 'bail|required|max:19',
			'promoId'               => 'bail|nullable|max:19',
			'name'                  => 'bail|required|max:255',
			'phone'                 => 'bail|required|max:14',
			'senderId'              => 'bail|required|max:19',
			'receiverId'            => 'bail|required|max:19',
			'debtorId'              => 'bail|required|max:19',
			'notes'                 => 'bail|required|max:500',
			'picktime'              => 'bail|date',
			'origin'                => 'bail|required|max:50',
			'destination_city'      => 'bail|required|max:50',
			'destination_district'  => 'bail|required|max:50',
			'items'                 => 'bail|required|array'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		DB::beginTransaction();

		// VALIDATE SENDER, RECEIVER, AND DEBTOR
		try {
			$address = $this->addressRepository->validateAddress($data, $data['userId']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// PROMO
		if ($data['promoId'] !== null) {
			if ($data['promoId'] !== '') {
				try {
					$promo = $this->promoRepository->getById($data['promoId']);
				} catch (Exception $e) {
					DB::rollback();
					Log::info($e->getMessage());
					Log::error($e);
					throw new InvalidArgumentException($e->getMessage());
				}

                // IF ANY PROMO, VALIDATE PROMO
				if ($promo !== false) {
                    try {
                        $this->promoRepository->validatePromoRepo($promo, $data['userId']);
                    } catch (Exception $e) {
                        DB::rollback();
                        Log::info($e->getMessage());
                        Log::error($e);
                        throw new InvalidArgumentException($e->getMessage());
                    }
				}

                // USE PROMO
                if ($promo !== null) {
                    if ($promo !== false) {
                        try {
                            $this->promoRepository->usePromoRepo($promo);
                        } catch (Exception $e) {
                            DB::rollback();
                            Log::info($e->getMessage());
                            Log::error($e);
                            throw new InvalidArgumentException('Promo gagal dipakai');
                        }
                    }
                }
                // END USE PROMO
			}
		}
		if ($data['promoId'] == null || $data['promoId'] == '') {
			$promo = null;
		}
		// END PROMO

		// GET ROUTE
		try {
			$route = $this->routeRepository->getRouteRepo($data);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
		}

		if (!$route) {
			DB::rollback();
			throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
		}
		// END GET ROUTE

		// SAVE PICKUP
		$data['senderId']   = $address['sender']['id'];
		$data['debtorId']   = $address['debtor']['id'];
		$data['receiverId'] = $address['receiver']['id'];
		try {
			$pickup = $this->pickupRepository->createPickupRepo($data, $promo);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan data pickup');
		}
		// END SAVE PICKUP

		// SAVE ITEM
        foreach ($data['items'] as $key => $value) {
            $validator = Validator::make($value, [
                'unit'          => 'bail|required',
                'unit_count'    => 'bail|required',
                'type'          => 'bail|required',
                'name'          => 'bail|required',
                'weight'        => 'bail|required',
                'volume'        => 'bail|required',
                'service_id'    => 'bail|nullable|present',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }
        }

		try {
			$items = $this->itemRepository->save($pickup, $data['items']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan item / barang');
		}
		// END SAVE ITEM

		// CALCULATE PRICE
		try {
			$price = $this->billRepository->calculatePriceRepo($items, $route, $promo);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
		}

		if (!$price->success) {
			DB::rollback();
			throw new InvalidArgumentException($price->message);
		}
		// END CALCULATE PRICE

		// CREATE TRACKING
		$tracking = [
			'pickupId' => $pickup['id'],
			'docs' => 'pickup',
			'status' => 'request',
			'notes' => 'pengajuan pickup order telah diterima',
			'picture' => null,
		];
		try {
			$this->trackingRepository->recordTrackingByPickupRepo($tracking);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan data tracking');
		}
		// END CREATE TRACKING

		DB::commit();
		$result = (object)[
			'items' => $items,
			'route' => $route,
			'pickup' => $pickup,
			'promo' => $promo,
			'price' => $price
		];
		return $result;
	}

	/**
	 * Get all pickup paginate
	 * @param array $data
	 */
	public function getAllPaginate($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getAllPickupPaginate($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException('Gagal mendapatkan data pickup');
		}
		return $pickup;
	}

	/**
	 * Get all pickup paginate
	 * @param array $data
	 */
	public function getReadyToPickupService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getReadyToPickupRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException('Gagal mendapatkan data pickup');
		}
		return $pickup;
	}

	/**
	 * get list pickup plan paginate
	 * @param array $data
	 */
	public function getListPickupPlanService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getListPickupPlanRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}
		return $pickup;
	}

	/**
	 * get list pickup inside pickup plan
	 * @param array $data
	 */
	public function getPickupByPickupPlanService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getPickupByPickupPlanRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $pickup;
	}

	/**
	 * Get pickup paginate by customer id
	 * @param array $data
	 */
	public function getPickupPaginateByUserId($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getPickupByCustomerRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException('Gagal mendapatkan data pickup');
		}
		return $pickup;
	}

	/**
	 * get pickup plan current driver
	 * @param array $data
	 */
	public function getDriverPickupPlanListService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getListPickupPlanDriverRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $pickup;
	}

	/**
	 * Get all pickup inside pickup plan paginate
	 * @param array $data
	 */
	public function getReadyToPickupDriverService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getReadyToPickupDriverRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException('Gagal mendapatkan data pickup');
		}
		return $pickup;
	}

	/**
	 * get list pickup inside pickup plan
	 * driver only
	 * @param array $data
	 */
	public function getPickupByPickupPlanDriverService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getPickupByPickupPlanDriverRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $pickup;
	}

	/**
	 * get total volume and kilo in pickup of pickup plan
	 * @param array $data
	 */
	public function getTotalVolumeAndKiloService($data = [])
	{
		$validator = Validator::make($data, [
			'pickupPlanId' => 'bail|required',
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		try {
			$result = $this->pickupRepository->getTotalVolumeAndKiloPickupRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $result;
	}

	/**
	 * get detail pickup
	 * @param array $data
	 */
	public function getDetailPickup($data = [])
	{
		$validator = Validator::make($data, [
			'pickupId' => 'bail|required',
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		try {
			$result = $this->pickupRepository->getDetailPickupRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}
		return $result;
	}

	/**
	 * get detail pickup for admin
	 * @param array $data
	 */
	public function getDetailPickupAdmin($data = [])
	{
		$validator = Validator::make($data, [
			'pickupId' => 'bail|required',
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		try {
			$result = $this->pickupRepository->getDetailPickupAdminRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $result;
	}

	/**
	 * create pickup service by admin
	 */
	public function createPickupAdminService($data = [])
	{
		$validator = Validator::make($data, [
			'items' => 'bail|required|array',
			'userId' => 'bail|required',
			'form' => 'bail|required',
			'customer' => 'bail|required',
			'isDrop' => 'boolean',
			'branchId' => 'bail|present',
            'marketing' => 'bail|present'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

        $validator = Validator::make($data['form'], [
			'name'                  => 'bail|required',
			'phone'                 => 'bail|required',
			'email'                 => 'bail|required',
			'fleetId'               => 'bail|required',
			'promoId'               => 'bail|present',
			'notes'                 => 'bail|present',
			'picktime'              => 'bail|required',
			'origin'                => 'bail|required',
			'destination_city'      => 'bail|required',
			'destination_district'  => 'bail|required',
            'newCustomer'           => 'bail|required|boolean',
            'sender'                => 'bail|required',
            'debtor'                => 'bail|required',
            'receiver'              => 'bail|required'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		DB::beginTransaction();

		// create user
		if ($data['form']['newCustomer'] == true) {

            $validator = Validator::make($data['customer'], [
                'email'     => 'bail|required',
                'name'      => 'bail|required',
                'branchId'  => 'bail|required',
                'phone'     => 'bail|required'
            ]);

            if ($validator->fails()) {
				DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }

            $username = explode("@", $data['customer']['email'], 2);
			$payload = [
				'email' => $data['customer']['email'],
				'name' => $data['customer']['name'],
				'username' => $username[0],
				'role_id' => 1,
				'branch_id' => $data['customer']['branchId'],
				'phone' => $data['customer']['phone'],
				'password' => 'user1234'
			];
			try {
				$customer = $this->userRepository->save($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException($e->getMessage());
			}
		} else {
			try {
				$customer = $this->userRepository->getByEmail($data['form']['email']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException($e->getMessage());
			}
		}

		// address customer
		$data['form']['sender']['is_primary'] = $data['form']['receiver']['is_primary'] = $data['form']['debtor']['is_primary'] = false;
		$data['form']['sender']['temporary'] = $data['form']['receiver']['temporary'] = $data['form']['debtor']['temporary'] = true;
		$data['form']['sender']['title'] = $data['form']['receiver']['title'] = $data['form']['debtor']['title'] = $data['form']['name'];
		$data['form']['sender']['userId'] = $data['form']['receiver']['userId'] = $data['form']['debtor']['userId'] = $customer['id'];

		// user web
		$data['form']['userId'] = $data['userId'];

        // ======= VALIDATOR ADDRESS =======
        // SENDER
        $validator = Validator::make($data['form']['sender'], [
            'is_primary'        => 'bail|required|boolean',
            'temporary'         => 'bail|required|boolean',
            'title'             => 'bail|required',
            'userId'            => 'bail|required',
            'province'          => 'bail|required',
            'city'              => 'bail|required',
            'district'          => 'bail|required',
            'village'           => 'bail|required',
            'street'            => 'bail|required',
            'postal_code'       => 'bail|required',
            'notes'             => 'bail|required'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            throw new InvalidArgumentException($validator->errors()->first().' (pada alamat pengirim)');
        }

        // RECEIVER
        $validator = Validator::make($data['form']['receiver'], [
            'is_primary'        => 'bail|required|boolean',
            'temporary'         => 'bail|required|boolean',
            'title'             => 'bail|required',
            'userId'            => 'bail|required',
            'province'          => 'bail|required',
            'city'              => 'bail|required',
            'district'          => 'bail|required',
            'village'           => 'bail|required',
            'street'            => 'bail|required',
            'postal_code'       => 'bail|required',
            'notes'             => 'bail|required',
            'name'              => 'bail|required',
            'phone'             => 'bail|required'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            throw new InvalidArgumentException($validator->errors()->first().' (pada alamat penerima)');
        }

        // DEBTOR
        $validator = Validator::make($data['form']['debtor'], [
            'is_primary'        => 'bail|required|boolean',
            'temporary'         => 'bail|required|boolean',
            'title'             => 'bail|required',
            'userId'            => 'bail|required',
            'province'          => 'bail|required',
            'city'              => 'bail|required',
            'district'          => 'bail|required',
            'village'           => 'bail|required',
            'street'            => 'bail|required',
            'postal_code'       => 'bail|required',
            'notes'             => 'bail|required',
            'name'              => 'bail|required',
            'phone'             => 'bail|required'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            throw new InvalidArgumentException($validator->errors()->first().' (pada alamat penagihan)');
        }
        // ======= END VALIDATOR ADDRESS =======

		// save sender
		try {
			$sender = $this->senderRepository->save($data['form']['sender']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// save debtor
		try {
			$debtor = $this->debtorRepository->save($data['form']['debtor']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// save receiver
		try {
			$receiver = $this->receiverRepository->save($data['form']['receiver']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

        // START PROMO
		if ($data['form']['promoId'] !== null) {
			if ($data['form']['promoId'] !== '') {
                // GET PROMO DATA
				try {
					$promo = $this->promoRepository->getById($data['form']['promoId']);
				} catch (Exception $e) {
					DB::rollback();
					Log::info($e->getMessage());
					Log::error($e);
					throw new InvalidArgumentException($e->getMessage());
				}

                // IF ANY PROMO, VALIDATE PROMO
				if ($promo !== false) {
                    try {
                        $this->promoRepository->validatePromoRepo($promo, $customer['id']);
                    } catch (Exception $e) {
                        DB::rollback();
                        Log::info($e->getMessage());
                        Log::error($e);
                        throw new InvalidArgumentException($e->getMessage());
                    }
				}

                // USED PROMO
                if ($promo !== null) {
                    if ($promo !== false) {
                        try {
                            $this->promoRepository->usePromoRepo($promo);
                        } catch (Exception $e) {
                            DB::rollback();
                            Log::info($e->getMessage());
                            Log::error($e);
                            throw new InvalidArgumentException('Promo gagal dipakai');
                        }
                    }
                }
                // END USED PROMO
			}
		}
		if ($data['form']['promoId'] == null || $data['form']['promoId'] == '') {
			$promo = null;
		}
		// END PROMO

		// GET ROUTE
		try {
			$route = $this->routeRepository->getRouteRepo($data['form']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
		}

		if (!$route) {
			DB::rollback();
			throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
		}
		// END GET ROUTE

		// SAVE PICKUP
		$data['form']['senderId'] = $sender['id'];
		$data['form']['receiverId'] = $receiver['id'];
		$data['form']['debtorId'] = $debtor['id'];
		try {
			$pickup = $this->pickupRepository->createPickupAdminRepo($data['form'], $promo, $customer, $data['isDrop']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan data pickup');
		}
		// END SAVE PICKUP

        // ADDING MARKETING
        if ($data['form']['withMarketing']) {
            $validator = Validator::make($data['marketing'], [
                'email'     => 'bail|required',
                'name'      => 'bail|required',
                'phone'     => 'bail|required'
            ]);

            if ($validator->fails()) {
				DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }

            // get marketing data
            $marketing = $this->roleRepository->getUserRoleByEmailRepo($data['marketing']['email']);

            if ($marketing->role->slug == 'marketing') {
                // update marketing on order
                /**
                 * marketing on order only update when value of "radio button with marketing" is true on create pickup/drop web
                 */
                $marketingId = $marketing->id;
                $orderId = $pickup['id'];
                try {
                    $this->pickupRepository->updateMarketingByOrderId($orderId, $marketingId);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException('(Gagal mengubah marketing ID di order) '.$e->getMessage());
                }

                // update marketing ID on customer
                $customerId = $customer['id'];
                try {
                    $this->userRepository->updateMarketingIdOnCustomer($customerId, $marketingId);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException('(Gagal mengubah marketing ID di customer) '.$e->getMessage());
                }

            }
        } else {
            // check current creator order is marketing or not
            $user = $this->userRepository->getById($data['userId']);

            // if marketing, update marketing id pada customer dan pickup
            if ($user->role->slug == 'marketing') {
                $marketingId = $marketing->id;
                $customerId = $customer['id'];

                // update marketing ID on customer
                try {
                    $this->userRepository->updateMarketingIdOnCustomer($customerId, $marketingId);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException('(Gagal mengubah marketing ID di customer) '.$e->getMessage());
                }
            }
        }


		// SAVE ITEM
		$items = $data['items'];
		foreach ($items as $key => $answer) {
			unset($items[$key]['service']);
		}

        // SAVE ITEM
        foreach ($data['items'] as $key => $value) {
            $validator = Validator::make($value, [
                'unit'          => 'bail|required',
                'unit_count'    => 'bail|required|numeric',
                'type'          => 'bail|required',
                'name'          => 'bail|required',
                'weight'        => 'bail|required|numeric',
                'volume'        => 'bail|required|numeric',
                'service_id'    => 'bail|nullable|present',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }
        }

		try {
			$items = $this->itemRepository->save($pickup, $items);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan item / barang');
		}
		// END SAVE ITEM

		// CALCULATE PRICE
		try {
			$price = $this->billRepository->calculatePriceRepo($items, $route, $promo);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
		}

		if (!$price->success) {
			DB::rollback();
			throw new InvalidArgumentException($price->message);
		}
		// END CALCULATE PRICE

		// ============= ONLY FOR DROP =============
		if ($data['isDrop']) {
			/**
			 * ======= PICKUP PLAN =========
			 */
			 // UPDATE BRANCH ID PADA PICKUP
			try {
				$this->pickupRepository->updateBranchRepo([$pickup['id']], $data['branchId']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Gagal mengupdate branch pada pickup order');
			}

			// SAVE PICKUP PLAN
			try {
				$result = $this->pickupPlanRepository->savePickupPlanRepo([$pickup['id']], 0, $data['userId']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Gagal menyimpan pickup plan');
			}
			// CREATE TRAKING DILEWATKAN KARENA DROP
			/**
			 * ========= END PICKUP PLAN =========
			 */

			/**
			 * ========== POP ===========
			 */
			// CREATE POP
			try {
				$payload = [
					'pickupId' => $pickup['id'],
					'driverPick' => false,
					'notes' => '-',
					'userId' => $data['userId'],
					'statusPick' => 'success',
					'popStatus' => 'applied'
				];
				$popResult = $this->popRepository->createPOPRepo($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Gagal membuat proof of pickup');
			}
			/**
			 * ========== END POP ===========
			 */


			 // START CALCULATE BILL
			try {
                $payload = [
                    'pickupId' => $pickup['id']
                ];
				$route = $this->routeRepository->getRouteByPickupRepo($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
			}

			if ($route == null) {
				throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
			}

			try {
				$promo = $this->promoRepository->getPromoByPickup($pickup['id']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, promo gagal ditemukan');
			}

			try {
                $payload = [
                    'pickupId' => $pickup['id']
                ];
				$items = $this->itemRepository->fetchItemByPickupRepo($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal mendapatkan items');
			}

			$items = collect($items)->values()->all();

			try {
				$bill = $this->billRepository->calculateAndSavePrice($items, $route, $promo);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menghitung total biaya');
			}

			if ($bill->success == false) {
				throw new InvalidArgumentException($bill->message);
			}

			$cost = [
				'pickupId' => $pickup['id'],
				'amount' => $bill->total_price
			];
			try {
				$cost = $this->costRepository->saveCostRepo($cost);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menyimpan total biaya');
			}

            // UPDATE PAYMENT
            try {
                $payload = [
                    'id' => $cost['id'],
                    'method' => $data['form']['paymentMethod'],
                    'dueDate' => $data['form']['paymentDueDate']
                ];
                $this->costRepository->updatePaymentMethod($payload);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal mengubah metode pembayaran');
            }
			// END CALCULATE BILL

            // TRACKING TIDAK DILAKUKAN KARENA MASUK KE DROP
		}
		DB::commit();
		$result = (object)[
			'items' => $items,
			'route' => $route,
			'pickup' => $pickup,
			'promo' => $promo,
			'price' => $price
		];
		return $result;
	}

	/**
	 * Get all pickup paginate where ready to shipment
	 * @param array $data
	 */
	public function getReadyToShipmentService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getReadyToShipmentRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException('Gagal mendapatkan data pickup');
		}
		return $pickup;
	}

	/**
	 * get list shipment plan paginate
	 * @param array $data
	 */
	public function getListShipmentPlanService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getListShipmentPlanRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $pickup;
	}

	/**
	 * get list pickup inside shipment plan
	 * @param array $data
	 */
	public function getPickupByShipmentPlanService($data = [])
	{
		try {
			$pickup = $this->pickupRepository->getPickupByShipmentPlanRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			throw new InvalidArgumentException($e->getMessage());
		}
		return $pickup;
	}

	/**
	 * create pickup service by admin
	 */
	public function createDropAdminService($data = [])
	{
		$validator = Validator::make($data, [
			'items' => 'bail|required',
			'userId' => 'bail|required',
			'form' => 'bail|required',
			'customer' => 'bail|required',
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		DB::beginTransaction();

		// create user
		if ($data['form']['newCustomer'] == true) {
			$username = explode("@", $data['customer']['email'], 2);
			$payload = [
				'email' => $data['customer']['email'],
				'name' => $data['customer']['name'],
				'username' => $username,
				'role_id' => 1,
				'branch_id' => $data['customer']['branchId'],
				'phone' => $data['customer']['phone'],
				'password' => 'user1234'
			];
			try {
				$customer = $this->userRepository->save($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException($e->getMessage());
			}
		} else {
			try {
				$customer = $this->userRepository->getByEmail($data['form']['email']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException($e->getMessage());
			}
		}

		// customer
		$data['form']['sender']['is_primary'] = $data['form']['receiver']['is_primary'] = $data['form']['debtor']['is_primary'] = false;
		$data['form']['sender']['temporary'] = $data['form']['receiver']['temporary'] = $data['form']['debtor']['temporary'] = true;
		$data['form']['sender']['title'] = $data['form']['receiver']['title'] = $data['form']['debtor']['title'] = $data['form']['name'];
		$data['form']['sender']['userId'] = $data['form']['receiver']['userId'] = $data['form']['debtor']['userId'] = $customer['id'];

		// user web
		$data['form']['userId'] = $data['userId'];

		// save sender
		try {
			$sender = $this->senderRepository->save($data['form']['sender']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// save debtor
		try {
			$debtor = $this->debtorRepository->save($data['form']['debtor']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// save receiver
		try {
			$receiver = $this->receiverRepository->save($data['form']['receiver']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}
		// PROMO
		if ($data['form']['promoId'] !== null) {
			if ($data['form']['promoId'] !== '') {
				try {
					$promo = $this->promoRepository->getById($data['form']['promoId']);
				} catch (Exception $e) {
					DB::rollback();
					Log::info($e->getMessage());
					Log::error($e);
					throw new InvalidArgumentException($e->getMessage());
				}

				if ($promo !== false) {
					if ($promo['user_id'] !== $data['userId']) {
						DB::rollback();
						throw new InvalidArgumentException('Promo tidak dapat digunakan');
					}
				}
			}
		}
		if ($data['form']['promoId'] == null || $data['form']['promoId'] == '') {
			$promo = null;
		}
		// END PROMO

		// GET ROUTE
		try {
			$route = $this->routeRepository->getRouteRepo($data['form']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
		}

		if (!$route) {
			DB::rollback();
			throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
		}
		// END GET ROUTE

		// SAVE PICKUP
		$data['form']['senderId'] = $sender['id'];
		$data['form']['receiverId'] = $receiver['id'];
		$data['form']['debtorId'] = $debtor['id'];
		try {
			$pickup = $this->pickupRepository->createPickupAdminRepo($data['form'], $promo, $customer);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan data pickup');
		}
		// END SAVE PICKUP

		// SAVE ITEM
		$items = $data['items'];
		foreach ($items as $key => $answer) {
			unset($items[$key]['service']);
		}
		try {
			$items = $this->itemRepository->save($pickup, $items);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal menyimpan item / barang');
		}
		// END SAVE ITEM

		// CALCULATE PRICE
		try {
			$price = $this->billRepository->calculatePriceRepo($items, $route, $promo);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
		}

		if (!$price->success) {
			DB::rollback();
			throw new InvalidArgumentException($price->message);
		}
		// END CALCULATE PRICE

		DB::commit();
		$result = (object)[
			'items' => $items,
			'route' => $route,
			'pickup' => $pickup,
			'promo' => $promo,
			'price' => $price
		];
		return $result;
	}

    /**
	 * edit pickup service by admin
	 */
	public function editPickupAdminService($data = [])
	{
		$validator = Validator::make($data, [
			'items' => 'bail|required',
			'userId' => 'bail|required',
			'form' => 'bail|required',
			'customer' => 'bail|required',
			'isDrop' => 'boolean',
			'branchId' => 'bail|present'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		DB::beginTransaction();

		// create user
		if ($data['form']['newCustomer'] == true) {
			$username = explode("@", $data['customer']['email'], 2);
			$payload = [
				'email' => $data['customer']['email'],
				'name' => $data['customer']['name'],
				'username' => $username,
				'role_id' => 1,
				'branch_id' => $data['customer']['branchId'],
				'phone' => $data['customer']['phone'],
				'password' => 'user1234'
			];
			try {
				$customer = $this->userRepository->firstOrCreateUserRepo($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException($e->getMessage());
			}
		} else {
			try {
				$customer = $this->userRepository->getByEmail($data['form']['email']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException($e->getMessage());
			}
		}

		// customer
		$data['form']['sender']['is_primary'] = $data['form']['receiver']['is_primary'] = $data['form']['debtor']['is_primary'] = false;
		$data['form']['sender']['temporary'] = $data['form']['receiver']['temporary'] = $data['form']['debtor']['temporary'] = true;
		$data['form']['sender']['title'] = $data['form']['receiver']['title'] = $data['form']['debtor']['title'] = $data['form']['name'];
		$data['form']['sender']['userId'] = $data['form']['receiver']['userId'] = $data['form']['debtor']['userId'] = $customer['id'];

		// user web
		$data['form']['userId'] = $data['userId'];

		// save sender
		try {
			$sender = $this->senderRepository->save($data['form']['sender']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// save debtor
		try {
			$debtor = $this->debtorRepository->save($data['form']['debtor']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		// save receiver
		try {
			$receiver = $this->receiverRepository->save($data['form']['receiver']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}
		// PROMO
		if ($data['form']['promoId'] !== null) {
			if ($data['form']['promoId'] !== '') {
				try {
					$promo = $this->promoRepository->getById($data['form']['promoId']);
				} catch (Exception $e) {
					DB::rollback();
					Log::info($e->getMessage());
					Log::error($e);
					throw new InvalidArgumentException($e->getMessage());
				}

				if ($promo !== false) {
					if ($promo['user_id'] !== $data['userId']) {
						DB::rollback();
						throw new InvalidArgumentException('Promo tidak dapat digunakan');
					}
				}
			}
		}
		if ($data['form']['promoId'] == null || $data['form']['promoId'] == '') {
			$promo = null;
		}
		// END PROMO

		// GET ROUTE
		try {
			$route = $this->routeRepository->getRouteRepo($data['form']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
		}

		if (!$route) {
			DB::rollback();
			throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
		}
		// END GET ROUTE

		// UPDATE PICKUP
		$data['form']['senderId'] = $sender['id'];
		$data['form']['receiverId'] = $receiver['id'];
		$data['form']['debtorId'] = $debtor['id'];
		try {
			$pickup = $this->pickupRepository->editPickupAdminRepo($data['form'], $promo, $customer, $data['isDrop']);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal mengubah data pickup');
		}
		// END UPDATE PICKUP

		// UPDATE ITEM
		$items = $data['items'];
		foreach ($items as $key => $answer) {
			unset($items[$key]['service']);
		}
		try {
			$items = $this->itemRepository->updateItemDrop($pickup, $items);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal mengubah item / barang');
		}
		// END UPDATE ITEM

		// CALCULATE PRICE
		try {
			$price = $this->billRepository->calculatePriceRepo($items, $route, $promo);
		} catch (Exception $e) {
			DB::rollback();
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
		}

		if (!$price->success) {
			DB::rollback();
			throw new InvalidArgumentException($price->message);
		}
		// END CALCULATE PRICE

		// ============= ONLY FOR DROP =============
		if ($data['isDrop']) {
			/**
			 * ======= PICKUP PLAN =========
			 */
			 // UPDATE BRANCH ID PADA PICKUP
			try {
				$this->pickupRepository->updateBranchRepo([$pickup['id']], $data['branchId']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Gagal mengupdate branch pada pickup order');
			}

			// SAVE PICKUP PLAN TIDAK ADA KARENA INI ADALAH EDIT DROP ORDER
			// CREATE TRAKING DILEWATKAN KARENA DROP
			/**
			 * ========= END PICKUP PLAN =========
			 */

			/**
			 * ========== POP ===========
			 */
			// CREATE POP TIDAK ADA KARENA INI ADALAH EDIT DROP ORDER
			/**
			 * ========== END POP ===========
			 */


			 // START CALCULATE BILL
			try {
                $payload = [
                    'pickupId' => $pickup['id']
                ];
				$route = $this->routeRepository->getRouteByPickupRepo($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
			}

			if ($route == null) {
				throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
			}

			try {
				$promo = $this->promoRepository->getPromoByPickup($pickup['id']);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
			}

			try {
                $payload = [
                    'pickupId' => $pickup['id']
                ];
				$items = $this->itemRepository->fetchItemByPickupRepo($payload);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal mendapatkan items');
			}

			$items = collect($items)->values()->all();

			try {
				$bill = $this->billRepository->calculateAndSavePrice($items, $route, $promo);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menghitung total biaya');
			}

			if ($bill->success == false) {
				throw new InvalidArgumentException($bill->message);
			}

			$cost = [
				'pickupId' => $pickup['id'],
				'amount' => $bill->total_price,
                'method' => $data['form']['paymentMethod'],
                'dueDate' => $data['form']['paymentDueDate']
			];
            // UPDATE COST
			try {
				$this->costRepository->editCostRepo($cost);
			} catch (Exception $e) {
				DB::rollback();
				Log::info($e->getMessage());
				Log::error($e);
				throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal mengubah total biaya dan metode pembayaran');
			}
			// END CALCULATE BILL

            // TRACKING TIDAK DILAKUKAN KARENA MASUK KE DROP
		}
		DB::commit();
		$result = (object)[
			'items' => $items,
			'route' => $route,
			'pickup' => $pickup,
			'promo' => $promo,
			'price' => $price
		];
		return $result;
	}

    /**
     * cancel drop order
     */
    public function cancelDropService($data = [])
    {
        $validator = Validator::make($data, [
			'userId' => 'bail|required',
			'pickupId' => 'bail|required'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		DB::beginTransaction();
        // CANCEL DROP
        try {
            $drop = $this->pickupRepository->cancelDropRepo($data['pickupId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // CANCEL PICKUP PLAN
        try {
            $pickupPlan = $this->pickupPlanRepository->cancelPickupPlanRepo($drop['pickup_plan_id']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // CANCEL POP
        try {
            $pop = $this->popRepository->cancelPopRepo($data['pickupId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        $result = [
            'pop' => $pop,
            'pickupPlan' -> $pickupPlan,
            'drop' => $drop
        ];

        DB::commit();
        return $result;
    }

    /**
     * get all order in branch
     */
    public function getOrderOnBranchService($branchId)
    {
        try {
            $order = $this->pickupRepository->getOrderOnBranchRepo($branchId);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $order;
    }
}
