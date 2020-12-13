<?php
namespace App\Services;

use App\Models\Address;
use App\Repositories\AddressRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class HelpService {

    protected $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Delete address by id.
     *
     * @param $id
     * @return String
     */
    public function deleteById($id)
    {
        DB::beginTransaction();

        try {
            $address = $this->addressRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete address data');
        }
        DB::commit();
        return $address;

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
     * Update address data
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function updateRole($data, $id)
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
    public function saveRoleData($data)
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

        $result = $this->addressRepository->save($data);

        return $result;
    }
}
