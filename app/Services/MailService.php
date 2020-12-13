<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\MailRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class MailService {

    protected $mailRepository;

    public function __construct(MailRepository $mailRepository)
    {
        $this->mailRepository = $mailRepository;
    }

    /**
     * send email verification.
     *
     * @param User $user
     * @param VerifyUser $verifyUser
     */
    public function sendEmailVerification($user, $verifyUser)
    {
        try {
            $this->mailRepository->sendEmailVerification($user, $verifyUser);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to send email verification');
        }
    }
}
