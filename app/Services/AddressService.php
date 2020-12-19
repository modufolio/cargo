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
            throw new InvalidArgumentException('Unable to delete address data');
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

            throw new InvalidArgumentException('Unable to update address data');
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
            'is_primary'    => 'bail|required|boolean',
            'userId'        => 'bail|required|integer',
            'title'         => 'bail|required|max:255',
            'receiptor'     => 'bail|required|max:255',
            'phone'         => 'bail|required|max:255',
            'province'      => 'bail|required|max:255',
            'city'          => 'bail|required|max:255',
            'district'      => 'bail|required|max:255',
            'village'       => 'bail|required|max:255',
            'postal_code'   => 'bail|required|integer|max:99999',
            'street'        => 'bail|required|max:255',
            'notes'         => 'bail|max:255'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $result = $this->addressRepository->save($data);

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
}
