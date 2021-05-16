<?php
namespace App\Services;

// use App\Models\Pickup;
use App\Repositories\PickupRepository;
// use App\Repositories\ItemRepository;
// use App\Repositories\BillRepository;
// use App\Repositories\PromoRepository;
// use App\Repositories\RouteRepository;
// use App\Repositories\AddressRepository;
// use App\Repositories\ProofOfPickupRepository;
// use App\Repositories\DebtorRepository;
// use App\Repositories\ReceiverRepository;
// use App\Repositories\SenderRepository;
// use App\Repositories\TrackingRepository;
// use App\Repositories\UserRepository;
// use App\Repositories\PickupPlanRepository;
// use App\Repositories\CostRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class FinanceService {

	protected $pickupRepository;
	// protected $itemRepository;
	// protected $billRepository;
	// protected $promoRepository;
	// protected $routeRepository;
	// protected $addressRepository;
	// protected $senderRepository;
	// protected $receiverRepository;
	// protected $debtorRepository;
	// protected $proofOfPickupRepository;
	// protected $trackingRepository;
	// protected $userRepository;
	// protected $pickupPlanRepository;
	// protected $costRepository;

	public function __construct(
		PickupRepository $pickupRepository
		// ItemRepository $itemRepository,
		// BillRepository $billRepository,
		// PromoRepository $promoRepository,
		// RouteRepository $routeRepository,
		// AddressRepository $addressRepository,
		// ProofOfPickupRepository $proofOfPickupRepository,
		// SenderRepository $senderRepository,
		// ReceiverRepository $receiverRepository,
		// DebtorRepository $debtorRepository,
		// TrackingRepository $trackingRepository,
		// UserRepository $userRepository,
		// PickupPlanRepository $pickupPlanRepository,
		// CostRepository $costRepository
	)
	{
		$this->pickupRepository = $pickupRepository;
		// $this->itemRepository = $itemRepository;
		// $this->billRepository = $billRepository;
		// $this->promoRepository = $promoRepository;
		// $this->routeRepository = $routeRepository;
		// $this->addressRepository = $addressRepository;
		// $this->popRepository = $proofOfPickupRepository;
		// $this->senderRepository = $senderRepository;
		// $this->receiverRepository = $receiverRepository;
		// $this->debtorRepository = $debtorRepository;
		// $this->trackingRepository = $trackingRepository;
		// $this->userRepository = $userRepository;
		// $this->pickupPlanRepository = $pickupPlanRepository;
		// $this->costRepository = $costRepository;
	}

	/**
     * get pickup for finance
	 * @param array $data
	 */
	public function getFinancePickupService($data = [])
	{
		$validator = Validator::make($data, [
            'perPage' => 'bail|required',
            'page' => 'bail|required',
            'sort' => 'bail|required',
            'number' => 'bail|present',
            'name' => 'bail|present',
            'receiver' => 'bail|present',
            'debtor' => 'bail|present',
            'paymentMethod' => 'bail|present',
			'branchId' => 'bail|required',
            'dateFrom' => 'bail|present',
            'dateTo' => 'bail|present'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

		try {
			$result = $this->pickupRepository->getFinishedPickupRepo($data);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			Log::error($e);
			throw new InvalidArgumentException($e->getMessage());
		}

		return $result;
	}
}
