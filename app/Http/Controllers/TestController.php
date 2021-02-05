<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

// MODEL
use App\Models\User;
use App\Models\Role;
use App\Models\Fleet;
use App\Models\Route;
use App\Models\Promo;

// SERVICE
use App\Services\AddressService;
use App\Services\PickupService;
use App\Services\PromoService;

// VENDOR
use Carbon\Carbon;
use Snowfire\Beautymail\Beautymail;
use Indonesia;

// MAIL
use App\Mail\VerifyMail;
use Illuminate\Support\Facades\Mail;

class TestController extends BaseController
{
    protected $addressService;
    protected $pickupService;
    protected $promoService;

    public function __construct(
        AddressService $addressService,
        PickupService $pickupService,
        PromoService $promoService
    )
    {
        $this->addressService = $addressService;
        $this->pickupService = $pickupService;
        $this->promoService = $promoService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::find(1);
        $user = [
            'user' => $user,
            'role' => $user->role()->get()
        ];
        return response()->json($user);
        $data = Indonesia::allProvinces();
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
        $arr = explode("@", $request->email, 2);
        dd($arr[0]);
        $data = Route::where([['origin',$request->origin],['destination', $request->destination]])->exists();

        return response()->json($data);
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
