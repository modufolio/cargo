<?php
namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;
use Illuminate\Validation\Rule;

class RoleService {

    protected $roleRepository;
    protected $userRepository;

    public function __construct(RoleRepository $roleRepository, UserRepository $userRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Delete role by id.
     *
     * @param array $data
     * @return String
     */
    public function deleteById($data)
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'roleId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // CHECK CURRENT ROLE USER
        try {
            $check = $this->roleRepository->isCurrentRoleEqualWithUserRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        if ($check == true) {
            throw new InvalidArgumentException('Maaf, peran yang sama dengan peran anda tidak bisa dihapus');
        }

        DB::beginTransaction();
        try {
            $role = $this->roleRepository->delete($data['roleId']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
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
    public function updateRoleService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'id' => 'bail|required',
            'name' => [
                'bail',
                'required',
                'min:2',
                Rule::unique('roles', 'name')->ignore($data['id'])
            ],
            'ranking' => 'bail|required',
            'description' => 'bail|present|max:255',
            'features' => 'bail|present|array',
            'privilleges' => 'bail|required|array'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $role = $this->roleRepository->updateRoleRepo($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
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
            'name' => 'bail|required|unique:roles,name',
            'ranking' => 'bail|required',
            'features' => 'bail|present|array',
            'description' => 'bail|required|max:255',
            'privilleges' => 'bail|present|array'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $result = $this->roleRepository->saveRoleRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     * validate and check role data.
     *
     * @param array $user
     * @return String
     */
    public function validateRoleLogin($user, $type)
    {
        if ($type == 'customer') {
            try {
                $result = $this->roleRepository->checkRoleCustomerRepo($user);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException($e->getMessage());
            }
        }
        if ($type == 'admin') {
            try {
                $result = $this->roleRepository->checkRoleAdminRepo($user);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException($e->getMessage());
            }
        }
        if ($type == 'driver') {
            try {
                $result = $this->roleRepository->checkRoleDriverRepo($user);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException($e->getMessage());
            }
        }
        return $result;
    }

    /**
     * Pagination role
     *
     * @param array $data
     */
    public function paginateRoleService($data = [])
    {
        try {
            $result = $this->roleRepository->rolePaginationRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data peran');
        }
        return $result;
    }

    /**
     * List feature
     */
    public function listFeatureService()
    {
        try {
            $result = $this->roleRepository->featureListRepo();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data fitur');
        }
        return $result;
    }

    /**
     * get menu for current role
     */
    public function getMenuRoleService($data = [])
    {
        try {
            $user = $this->userRepository->getByIdWithRole($data['userId']);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        $menu = collect($user['role']['privilleges'])->map(function($priv) {
            $access = explode("_", $priv);
            $data = [
                'type' => $access[0],
                'slug' => $access[1]
            ];
            return $data;
        });

        $menuSlug = $submenuSlug = [];
        foreach ($menu as $key => $value) {
            if ($value['type'] == 'menu') {
                $menuSlug[] = $value['slug'];
            }
            if ($value['type'] == 'submenu') {
                $submenuSlug[] = $value['slug'];
            }
        }

        $payload = [
            'menuSlug' => $menuSlug,
            'submenuSlug' => $submenuSlug
        ];

        try {
            $result = $this->roleRepository->getMenuRoleRepo($payload);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data fitur');
        }
        return $result;
    }
}
