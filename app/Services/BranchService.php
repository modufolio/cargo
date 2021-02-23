<?php
namespace App\Services;

use App\Models\Address;
use App\Repositories\BranchRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class BranchService {

    protected $branchRepository;

    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    /**
     * Delete branch by id.
     *
     * @param int $id
     * @return Branch
     */
    public function deleteById($id)
    {
        DB::beginTransaction();

        try {
            $branch = $this->branchRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus branch');
        }
        DB::commit();
        return $branch;
    }

    /**
     * Get all branch.
     *
     * @return Branch
     */
    public function getAllBranchService()
    {
        try {
            $branch = $this->branchRepository->getAllBranchRepo();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat semua branch');
        }
        return $branch;
    }

    /**
     * Get all branch paginate.
     * @param array $data
     * @return mixed
     */
    public function getAllPaginate($data)
    {
        try {
            $branch = $this->branchRepository->getAllPaginateRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat semua branch');
        }
        return $branch;
    }

    /**
     * Get branch by id.
     *
     * @param int $id
     * @return Branch
     */
    public function getById($id)
    {
        try {
            $branch = $this->branchRepository->getById($id);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data branch');
        }
        return $branch;
    }

    /**
     * Update branch data
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
            $branch = $this->branchRepository->update($data, $id);

        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());

            throw new InvalidArgumentException('Gagal mengubah data branch');
        }

        DB::commit();

        return $branch;

    }

    /**
     * Validate branch data.
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return Branch
     */
    public function createBranchService($data)
    {
        $validator = Validator::make($data, [
            'name' => 'bail|required|max:255',
            'province' => 'bail|required|max:255',
            'city' => 'bail|required|max:255',
            'district' => 'bail|required|max:255',
            'village' => 'bail|required|max:255',
            'postalCode' => 'bail|required|numeric|max:255',
            'street' => 'bail|required|max:99999',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->branchRepository->saveBranchRepo($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menambah data cabang');
        }
        DB::commit();
        return $result;
    }

    /**
     * Validate branch data.
     * update to DB if there are no errors.
     *
     * @param array $data
     * @return Branch
     */
    public function updateBranchService($data)
    {
        $validator = Validator::make($data, [
            'id' => 'bail|required',
            'name' => 'bail|required|max:255',
            'province' => 'bail|required|max:255',
            'city' => 'bail|required|max:255',
            'district' => 'bail|required|max:255',
            'village' => 'bail|required|max:255',
            'postalCode' => 'bail|required|numeric|max:255',
            'street' => 'bail|required|max:99999',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->branchRepository->updateBranchRepo($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal merubah data cabang');
        }
        DB::commit();
        return $result;
    }
}
