<?php
namespace App\Services;

use App\Models\Promo;
use App\Repositories\PromoRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;
class PromoService {

    protected $promoRepository;

    public function __construct(PromoRepository $promoRepository)
    {
        $this->promoRepository = $promoRepository;
    }

    /**
     * Get promo for current user.
     *
     * @return Promo
     */
    public function getPromoUser($data)
    {
        $validator = Validator::make($data, [
            'userId'                => 'bail|required|max:19',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $promo = $this->promoRepository->getUserId($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan promo untuk pengguna ini');
        }
        return $promo;
    }

    /**
     * Get creator promo.
     *
     * @return Promo
     */
    public function getPromoCreator($data)
    {
        $validator = Validator::make($data, [
            'userId'                => 'bail|required|max:19',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $promo = $this->promoRepository->getCreatedBy($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan promo yang telah dibuat pengguna ini');
        }
        return $promo;
    }
}
