<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class AuthService {

    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * get access token.
     *
     * @param String $email
     * @param String $pass
     * @return String
     */
    public function getAccessToken($email, $pass)
    {
        DB::beginTransaction();
        try {
            $data = $this->authRepository->getAccessToken($email, $pass);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to get access token');
        }
        DB::commit();
        return $data;
    }

    /**
     * Get all address.
     *
     * @param String $refreshToken
     * @return Mixed
     */
    public function refreshToken($refreshToken)
    {
        DB::beginTransaction();
        try {
            $data = $this->authRepository->refreshToken($refreshToken);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to get refresh token');
        }
        DB::commit();
        return $data;
    }

    /**
     * create verify user.
     *
     * @param String $id
     * @return Mixed
     */
    public function createVerifyUser($id)
    {
        DB::beginTransaction();
        try {
            $data = $this->authRepository->createVerifyUser($id);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to create verify user');
        }
        DB::commit();
        return $data;
    }
}
