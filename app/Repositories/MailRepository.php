<?php

namespace App\Repositories;

use App\Models\User;
use Snowfire\Beautymail\Beautymail;

class MailRepository
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Send verify email token
     *
     * @param User $user
     * @param VerifyUser $verifyUser
     */
    public function sendEmailVerification($user, $verifyUser)
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.verify', ['user' => $user, 'verify' => $verifyUser], function($message) use ($user)
        {
            $message
                // ->from('ival@papandayan.com')
                ->from(env('MAIL_FROM_ADDRESS'))
                ->to($user->email, $user->name)
                ->subject('Selamat bergabung!');
        });
    }

    /**
     * Send email forgot password
     *
     * @param User $user
     */
    public function sendEmailForgotPassword($user, $newPass)
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.forgotpass', ['user' => $user, 'newPass' => $newPass], function($message) use ($user)
        {
            $message
                // ->from('ival@papandayan.com')
                ->from(env('MAIL_FROM_ADDRESS'))
                ->to($user->email, $user->name)
                ->subject('Password telah diubah!');
        });
    }
}
