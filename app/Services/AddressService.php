<?php
namespace App\Services;

use App\Models\Address;
use App\Repositories\AddressRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class AddressService {

    protected $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Get all address.
     *
     * @return String
     */
    public function getAll()
    {
        return $this->addressRepository->getAll();
    }

    /**
     * Get address by id.
     *
     * @param $id
     * @return String
     */
    public function getById($id)
    {
        return $this->addressRepository->getById($id);
    }

    /**
     * Delete address by id.
     *
     * @param $id
     * @return String
     */
    public function deleteById($addressId)
    {
        DB::beginTransaction();
        try {
            $address = $this->addressRepository->delete($addressId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus alamat');
        }
        DB::commit();
        return $address;

    }

    /**
     * Update address data
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function updateAddress($data, $id)
    {
        $validator = Validator::make($data, [
            'name' => 'bail|min:2',
            'slug' => 'bail|max:255',
            'ranking' => 'bail|max:255',
            'features' => 'bail|max:255',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $address = $this->addressRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengubah alamat');
        }
        DB::commit();
        return $address;
    }

    /**
     * Validate address data.
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function saveAddressData($data)
    {
        $validator = Validator::make($data, [
            'userId'        => 'bail|required|integer',
            'province'      => 'bail|required|max:255',
            'city'          => 'bail|required|max:255',
            'district'      => 'bail|required|max:255',
            'village'       => 'bail|required|max:255',
            'postal_code'   => 'bail|required|numeric|max:999999',
            'street'        => 'bail|required|max:255',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->addressRepository->save($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan alamat');
        }
        DB::commit();
        return $result;
    }

    /**
     * Get address by given user id
     */
    public function getAddressUser($userId)
    {
        DB::beginTransaction();

        try {
            $address = $this->addressRepository->getByUserId($userId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to get user address');
        }

        DB::commit();

        return $address;
    }

    /**
     * search address customer
     */
    public function searchCustomerAddressService($data = [])
    {
        $validator = Validator::make($data, [
            'type' => 'bail|required|string',
            'id' => 'bail|required',
            'query' => 'bail|present',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $address = $this->addressRepository->searchCustomerAddressRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal mendapat data alamat customer');
        }
        return $address;
    }
}
