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
            $branch = $this->branchRepository->getPaginate($data);
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
    public function save($data)
    {
        $validator = Validator::make($data, [
            'name' => 'bail|required|max:255',
            'email' => 'bail|required|max:255|email|unique:users',
            'password' => 'bail|required|max:255|confirmed',
            'role_id' => 'bail|required|max:1',
            'username' => 'bail|required|max:255|unique:users,username',
            'phone' => 'bail|max:15|unique:users,phone',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $result = $this->branchRepository->save($data);

        return $result;
    }
}
