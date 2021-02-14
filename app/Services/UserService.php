<?php
namespace App\Services;

use App\Models\Address;
use App\Repositories\UserRepository;
use App\Repositories\AddressRepository;
use App\Repositories\MailRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Illuminate\Http\Request;

class UserService {

    protected $userRepository;
    protected $addressRepository;
    protected $mailRepository;

    public function __construct(
        UserRepository $userRepository,
        AddressRepository $addressRepository,
        MailRepository $mailRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
        $this->mailRepository = $mailRepository;
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

        DB::beginTransaction();
        try {
            $result = $this->userRepository->save($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan data pengguna');
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
    public function updateUserService($data)
    {
        $validator = Validator::make($data, [
            'name' => 'bail|required|max:255',
            'username' => [
                'bail',
                'required',
                'max:255',
                'alpha_num',
                Rule::unique('users', 'username')->ignore($data['id'])
            ],
            'phone' => [
                'bail',
                'max:15',
                Rule::unique('users', 'phone')->ignore($data['id'])
            ],
            'id' => 'bail|required',
            'role' => 'bail|required|max:50',
            'branch' => 'bail|required|max:255',
            'province' => 'bail|required|max:255',
            'city' => 'bail|required|max:255',
            'district' => 'bail|required|max:255',
            'village' => 'bail|required|max:255',
            'street' => 'bail|required|max:255',
            'postalCode' => 'bail|required|max:99999',
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
            throw new InvalidArgumentException($e->getMessage());
        }

        $address = [
            'postal_code' => $data['postalCode']
        ];
        $address = array_merge($address, $data);

        try {
            $this->addressRepository->update($address, $data['id']);
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

    /**
     * Change password
     */
    public function changePasswordService($data)
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'password' => 'bail|required|min:8|max:255|confirmed',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $user = $this->userRepository->changePasswordRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $user;
    }

    /**
     * Forgot Password
     */
    public function forgotPasswordService($data)
    {
        DB::beginTransaction();
        try {
            $result = $this->userRepository->forgotPasswordRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        // send email verification
        try {
            $this->mailRepository->sendEmailForgotPassword($result['user'], $result['newPass']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     *
     * Update user profile mobile.
     *
     * @param array $data
     * @return String
     */
    public function updateUserProfileService($data)
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'name' => 'bail|required|max:255',
            'username' => [
                'bail',
                'required',
                'max:255',
                'alpha_num',
                Rule::unique('users', 'username')->ignore($data['userId'])
            ],
            'phone' => [
                'bail',
                'max:15',
                Rule::unique('users', 'phone')->ignore($data['userId'])
            ],
            'avatar' => 'bail|required',
        ]);
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            $result = $this->userRepository->updateUserProfileRepo($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     * upload avatar service
     *
     * @param array $data
     * @return object
     */
    public function uploadAvatarService($request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|file|image|max:512|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->userRepository->uploadAvatar($request);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengunggah avatar');
        }
        DB::commit();
        return $result;
    }
}
