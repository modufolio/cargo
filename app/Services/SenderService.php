<?php
namespace App\Services;

use App\Models\Sender;
use App\Repositories\SenderRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class SenderService {

    protected $senderRepository;

    public function __construct(SenderRepository $senderRepository)
    {
        $this->senderRepository = $senderRepository;
    }

    /**
     * Get all sender.
     *
     * @return String
     */
    public function getAll()
    {
        return $this->senderRepository->getAll();
    }

    /**
     * Get sender by id.
     *
     * @param $id
     * @return String
     */
    public function getById($id)
    {
        return $this->senderRepository->getById($id);
    }

    /**
     * Get sender by given user id
     */
    public function getByUserId($userId)
    {
        DB::beginTransaction();

        try {
            $sender = $this->senderRepository->getByUserId($userId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan alamat pengirim');
        }

        DB::commit();

        return $sender;
    }

    /**
     * Delete sender by id.
     *
     * @param $id
     * @return String
     */
    public function deleteById($id, $userId)
    {
        DB::beginTransaction();
        try {
            $address = $this->senderRepository->getById($id);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat alamat pengirim (code: 5001)');
        }

        if ($address['user_id'] !== $userId) {
            throw new InvalidArgumentException('Pengguna tidak bisa menghapus alamat pengirim ini');
        }

        try {
            $sender = $this->senderRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus alamat pengirim (code: 5002)');
        }
        DB::commit();
        return $sender;

    }

    /**
     * Update sender data
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
            $sender = $this->senderRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengubah alamat');
        }
        DB::commit();
        return $sender;
    }

    /**
     * Validate sender data.
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function save($data)
    {
        $validator = Validator::make($data, [
            'is_primary'    => 'bail|required|boolean',
            'temporary'     => 'bail|required|boolean',
            'userId'        => 'bail|required|integer',
            'title'         => 'bail|required|max:255',
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
            $result = $this->senderRepository->save($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan alamat pengirim');
        }
        DB::commit();
        return $result;
    }
}
