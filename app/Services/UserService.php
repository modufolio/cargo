<?php
namespace App\Services;

use App\Models\Address;
use App\Repositories\UserRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class UserService {

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Delete user by id.
     *
     * @param $id
     * @return String
     */
    public function deleteById($id)
    {
        DB::beginTransaction();

        try {
            $user = $this->userRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menghapus user');
        }
        DB::commit();
        return $user;

    }

    /**
     * Get all user.
     *
     * @return String
     */
    public function getAll()
    {
        try {
            $user = $this->userRepository->getAll();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat semua user');
        }
        return $user;
    }

    /**
     * Get all user paginate.
     *
     * @return String
     */
    public function getAllPaginate($data)
    {
        try {
            $user = $this->userRepository->getPaginate($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat semua user');
        }
        return $user;
    }

    /**
     * Get user by id.
     *
     * @param $id
     * @return String
     */
    public function getById($id)
    {
        try {
            $user = $this->userRepository->getById($id);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data user');
        }
        return $user;
    }

    /**
     * Update user data
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
            $user = $this->userRepository->updateUserRepo($data, $id);

        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());

            throw new InvalidArgumentException('Gagal mengubah data user');
        }

        DB::commit();

        return $user;

    }

    /**
     * Validate user data.
     * Store to DB if there are no errors.
     *
     * @param array $data
     * @return String
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

        $result = $this->userRepository->save($data);

        return $result;
    }

    /**
     * Validate user data.
     * Update to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function updateUserService($data)
    {
        $validator = Validator::make($data, [
            'name' => 'bail|required|max:255',
            'username' => [
                'bail',
                'required',
                'max:255',
                Rule::unique('users', 'username')->ignore($data['userId'])
            ],
            'phone' => [
                'bail',
                'max:15',
                Rule::unique('users', 'phone')->ignore($data['userId'])
            ]
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $result = $this->userRepository->updateUserRepo($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengubah data user');
        }

        DB::commit();


        return $result;
    }

    /**
     * Validate user data.
     * Update to DB if there are no errors.
     *
     * @param array $data
     * @return String
     */
    public function updateBranchService($data)
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'branchId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $result = $this->userRepository->updateBranchRepo($data);

        return $result;
    }

    /**
     * Get user by name.
     *
     * @param string $name
     * @return String
     */
    public function getByNameService($name)
    {
        try {
            $user = $this->userRepository->getByNameRepo($name);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data user');
        }
        return $user;
    }

    /**
     * Get user by email.
     *
     * @param string $email
     * @return String
     */
    public function getByEmailService($email)
    {
        try {
            $user = $this->userRepository->getByEmailRepo($email);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data user');
        }
        return $user;
    }
}
