<?php
namespace App\Services;

use App\Models\Receiver;
use App\Repositories\ReceiverRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class ReceiverService {

    protected $receiverRepository;

    public function __construct(ReceiverRepository $receiverRepository)
    {
        $this->receiverRepository = $receiverRepository;
    }

    /**
     * Get all receiver.
     *
     * @return String
     */
    public function getAll()
    {
        return $this->receiverRepository->getAll();
    }

    /**
     * Get receiver by id.
     *
     * @param $id
     * @return String
     */
    public function getById($id)
    {
        return $this->receiverRepository->getById($id);
    }

    /**
     * Get receiver by given user id
     */
    public function getByUserId($userId)
    {
        DB::beginTransaction();

        try {
            $receiver = $this->receiverRepository->getByUserId($userId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan alamat penerima');
        }

        DB::commit();

        return $receiver;
    }

    /**
     * Delete receiver by id.
     *
     * @param $id
     * @param $userId
     * @return String
     */
    public function deleteById($id, $userId)
    {
        DB::beginTransaction();

        try {
            $receiver = $this->receiverRepository->delete($id, $userId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus alamat penerima (code: 5001)');
        }

        if (!$receiver) {
            DB::rollBack();
            throw new InvalidArgumentException('Pengguna tidak bisa menghapus alamat penerima ini');
        }

        DB::commit();
        return $receiver;

    }

    /**
     * Update receiver data
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function update($data, $id)
    {
        $validator = Validator::make($data, [
            'userId'        => 'bail|required|integer',
            'title'         => 'bail|required|max:255',
            'name'          => 'bail|required|max:255',
            'phone'         => 'bail|required|max:14',
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

        DB::beginTransaction();
        try {
            $receiver = $this->receiverRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengubah alamat penerima');
        }

        if (!$receiver) {
            DB::rollBack();
            throw new InvalidArgumentException('Pengguna tidak bisa mengubah alamat penerima ini');
        }

        DB::commit();
        return $receiver;
    }

    /**
     * Validate receiver data.
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function save($data)
    {
        $validator = Validator::make($data, [
            'temporary'     => 'bail|required|boolean',
            'userId'        => 'bail|required|integer',
            'title'         => 'bail|required|max:255',
            'phone'         => 'bail|required|max:14',
            'name'          => 'bail|required|max:255',
            'province'      => 'bail|required|max:255',
            'city'          => 'bail|required|max:255',
            'district'      => 'bail|required|max:255',
            'village'       => 'bail|required|max:255',
            'postal_code'   => 'bail|required',
            'street'        => 'bail|required|max:255',
            'notes'         => 'bail|max:255'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->receiverRepository->save($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan alamat penerima');
        }
        DB::commit();
        return $result;
    }
}
