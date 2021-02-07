<?php
namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class RoleService {

    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Delete role by id.
     *
     * @param $id
     * @return String
     */
    public function deleteById($id)
    {
        DB::beginTransaction();

        try {
            $role = $this->roleRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus data peran');
        }
        DB::commit();
        return $role;

    }

    /**
     * Get all role.
     *
     * @return String
     */
    public function getAll()
    {
        try {
            $role = $this->roleRepository->getAll();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat semua data peran');
        }
        return $role;
    }

    /**
     * Get role by id.
     *
     * @param $id
     * @return String
     */
    public function getById($id)
    {
        return $this->roleRepository->getById($id);
    }

    /**
     * Update role data
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
            $role = $this->roleRepository->update($data, $id);

        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());

            throw new InvalidArgumentException('Unable to update role data');
        }

        DB::commit();

        return $role;

    }

    /**
     * Validate role data.
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

        $result = $this->roleRepository->save($data);

        return $result;
    }

    /**
     * validate and check role data.
     *
     * @param array $user
     * @return String
     */
    public function validateRoleLogin($user)
    {
        try {
            $result = $this->roleRepository->checkRoleRepo($user);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }
}
