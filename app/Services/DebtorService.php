<?php
namespace App\Services;

use App\Models\Debtor;
use App\Repositories\DebtorRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class DebtorService {

    protected $debtorRepository;

    public function __construct(DebtorRepository $debtorRepository)
    {
        $this->debtorRepository = $debtorRepository;
    }

    /**
     * Get all debtor.
     *
     * @return String
     */
    public function getAll()
    {
        return $this->debtorRepository->getAll();
    }

    /**
     * Get debtor by id.
     *
     * @param $id
     * @return String
     */
    public function getById($id)
    {
        return $this->debtorRepository->getById($id);
    }

    /**
     * Get debtor by given user id
     */
    public function getByUserId($userId)
    {
        DB::beginTransaction();
        try {
            $debtor = $this->debtorRepository->getByUserId($userId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan alamat penagihan');
        }
        DB::commit();
        return $debtor;
    }

    /**
     * Delete debtor by id.
     *
     * @param $id
     * @param $userId
     * @return String
     */
    public function deleteById($id, $userId)
    {
        DB::beginTransaction();
        try {
            $debtor = $this->debtorRepository->delete($id, $userId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus alamat penagihan (code: 5002)');
        }
        if (!$debtor) {
            DB::rollBack();
            throw new InvalidArgumentException('Pengguna tidak bisa menghapus alamat penagihan ini');
        }
        DB::commit();
        return $debtor;

    }

    /**
     * Update debtor data
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return array
     */
    public function update($data, $id)
    {
        $validator = Validator::make($data, [
            'userId'        => 'bail|required|integer',
            'title'         => 'bail|nullable|max:255',
            'phone'         => 'bail|required|max:14',
            'name'          => 'bail|required|max:255',
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
            $debtor = $this->debtorRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengubah alamat penagihan');
        }

        if (!$debtor) {
            DB::rollBack();
            throw new InvalidArgumentException('Pengguna tidak bisa mengubah alamat penagihan ini');
        }

        DB::commit();
        return $debtor;
    }

    /**
     * Validate debtor data.
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function save($data)
    {
        $validator = Validator::make($data, [
            'userId'        => 'bail|required|integer',
            'title'         => 'bail|required|max:255',
            'phone'         => 'bail|required|max:14',
            'name'          => 'bail|required|max:255',
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
            $result = $this->debtorRepository->save($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan alamat penagihan');
        }
        DB::commit();
        return $result;
    }
}
