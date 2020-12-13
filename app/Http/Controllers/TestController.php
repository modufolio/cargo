<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Snowfire\Beautymail\Beautymail;

use App\Models\User;
use App\Models\Role;
use App\Mail\VerifyMail;
use App\Http\Controllers\BaseController;

class TestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::findOrFail(1);
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.verify', ['user' => $user], function($message) use ($user)
        {
            // dd($message);
            $message
                // ->from('ival@papandayan.com')
                ->from(env('MAIL_FROM_ADDRESS'))
                ->to($user->email, $user->name)
                ->subject('Selamat bergabung!');
        });

        // Mail::to($user)->send(new VerifyMail($user));
        // $data = User::find(1)->setAppends(['features','role'])->toArray();
        // $data = collect($data)->only('id', 'email', 'name','features','role')->all();
        return $this->sendResponse('data user', $user);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
