<?php
namespace App\Services;

use App\Repositories\CostRepository;
use App\Repositories\PickupRepository;
// use App\Models\Pickup;
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
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class FinanceService {

	protected $pickupRepository;
	protected $costRepository;
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

	public function __construct(
		PickupRepository $pickupRepository,
		CostRepository $costRepository
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
	)
	{
		$this->pickupRepository = $pickupRepository;
		$this->costRepository = $costRepository;
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
			'branchName' => 'bail|required',
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

    /**
     * update cost service
     */
    public function updateFinanceCostService($data = [])
    {
        $validator = Validator::make($data, [
            'cost' => 'bail|required',
            'userId' => 'bail|required',
            'extraCosts' => 'bail|present'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

        $validator = Validator::make($data['cost'], [
            'id' => 'bail|required',
            'amount' => 'bail|required|min:0|numeric',
            'discount' => 'bail|required|min:0|numeric',
            'method' => 'bail|required',
            'due_date' => 'bail|present',
            'clear_amount' => 'bail|required|min:0|numeric',
            'amount_with_service' => 'bail|required|min:0|numeric',
            'status' => 'bail|required',
            'notes' => 'bail|present',
            'service' => 'bail|required|min:0|numeric'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

        DB::beginTransaction();

        // UPDATE COST
        try {
            $payload = [
                'amount' => $data['cost']['amount'],
                'discount' => $data['cost']['discount'],
                'method' => $data['cost']['method'],
                'dueDate' => $data['cost']['due_date'],
                'clearAmount' => $data['cost']['clear_amount'],
                'status' => $data['cost']['status'],
                'notes' => $data['cost']['notes'],
                'service' => $data['cost']['service'],
                'id' => $data['cost']['id'],
                'amount_with_service' => $data['cost']['amount_with_service']
            ];
			$cost = $this->costRepository->updateCostRepo($payload);
		} catch (Exception $e) {
			Log::info($e->getMessage());
			Log::error($e);
            DB::rollback();
			throw new InvalidArgumentException($e->getMessage());
		}
        // END UPDATE COST

        // UPDATE EXTRA COST
        $extraCost = $data['extraCosts'];
        if (count($extraCost) > 0) {
            // DELETE EXTRA COST BY COSTID
            try {
                $this->costRepository->deleteExtraCostByCostIdRepo($data['cost']['id']);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                Log::error($e);
                DB::rollback();
                throw new InvalidArgumentException($e->getMessage());
            }

            $extra = [];
            foreach ($extraCost as $key => $value) {
                $validator = Validator::make($value, [
                    'amount' => 'bail|required|min:0',
                    'notes' => 'bail|present'
                ]);

                if ($validator->fails()) {
                    DB::rollback();
                    throw new InvalidArgumentException('Gagal menyimpan biaya tambahan. '. $validator->errors()->first());
                }

                try {
                    $payload = [
                        'amount' => $value['amount'],
                        'notes' => $value['notes'],
                        'userId' => $data['userId'],
                        'costId' => $data['cost']['id']
                    ];
                    $extra[] = $this->costRepository->saveExtraCostRepo($payload);
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                    Log::error($e);
                    DB::rollback();
                    throw new InvalidArgumentException($e->getMessage());
                }
            }
        }
        // END UPDATE EXTRA COST
        DB::commit();
		return [
            'cost' => $cost,
            'extraCosts' => $extra
        ];
    }
}
