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
            'userId' => 'bail|required|max:19',
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
            Log::error($e);
            throw new InvalidArgumentException('Gagal mendapatkan promo yang telah dibuat pengguna ini');
        }
        return $promo;
    }

    /**
     * Select promo
     *
     * @param array $data
     * @return mixed
     */
    public function selectPromo($data)
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required|max:19',
            'promoId' => 'bail|required|max:19',
            'value' => 'bail|required|max:10',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // get promo
        try {
            $promo = $this->promoRepository->getById($data['promoId']);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Promo tidak ditemukan');
        }

        try {
            $this->promoRepository->validatePromo($promo, $data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        try {
            $result = $this->promoRepository->selectPromo($promo, $data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menggunakan promo ini');
        }

        return $result;
    }

    /**
     * Get all promo paginate.
     * @param array $data
     * @return mixed
     */
    public function getPromoPaginateService($data)
    {
        try {
            $branch = $this->promoRepository->getAllPaginateRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat semua promo');
        }
        return $branch;
    }

    /**
     * create promo service
     *
     * @param array $data
     */
    public function createPromoService($data)
    {
        $validator = Validator::make($data, [
            'code' => 'bail|required',
            'customerId' => 'bail|required',
            'description' => 'bail|required',
            'discount' => 'bail|required',
            'discountMax' => 'bail|required',
            'endAt' => 'bail|required',
            'maxUsed' => 'bail|required',
            'minValue' => 'bail|required',
            'startAt' => 'bail|required',
            'terms' => 'bail|required',
            'userId' => 'bail|required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $promo = $this->promoRepository->save($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            DB::rollback();
            throw new InvalidArgumentException('Gagal membuat promo');
        }
        DB::commit();
        return $promo;
    }

    /**
     * update promo
     */
    public function updatePromoService($data = [])
    {
        $validator = Validator::make($data, [
            'description' => 'bail|required',
            'discount' => 'bail|required',
            'discount_max' => 'bail|required',
            'end_at' => 'bail|required',
            'start_at' => 'bail|required',
            'max_used' => 'bail|required',
            'min_value' => 'bail|required',
            'terms' => 'bail|required',
            'userId' => 'bail|required',
            'id' => 'bail|required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $promo = $this->promoRepository->updatePromoRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            DB::rollback();
            throw new InvalidArgumentException('Gagal mengubah data promo');
        }
        DB::commit();
        return $promo;
    }
}
