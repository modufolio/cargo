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
use App\Models\Item;
use App\Models\Pickup;
use App\Models\PickupPlan;
use App\Models\ShipmentPlan;
use App\Models\ProofOfDelivery;
use App\Models\ProofOfPickup;

// SERVICE
use App\Services\AddressService;
use App\Services\PickupService;
use App\Services\PromoService;

// VENDOR
use Carbon\Carbon;
use Snowfire\Beautymail\Beautymail;
use Indonesia;
use Haruncpi\LaravelIdGenerator\IdGenerator;

// UTILITIES
use App\Utilities\RandomStringGenerator;

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
        $carbon = Carbon::parse('2019-05-19 15:48:19')->diffInSeconds(Carbon::now('Asia/Jakarta'), false);
        // if ($carbon > 30) {
            return 'aa => '.$carbon;
        // }
        // return 'bb => '.$carbon;
        $data = Pickup::find(50);
        $pop = $data->proofOfPickup;
        $pop->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
        $pop->save();
        return response()->json($pop);
        $items = collect($data)->flatten()->toArray();
        // return $items;
        $volume = array_sum(array_column($items, 'volume'));
        $weight = array_sum(array_column($items, 'weight'));
        $result = [
            'volume' => $volume,
            'weight' => $weight
        ];
        return response()->json($result);
        $data = ProofOfDelivery::where('pickup_id', 40)->select('redelivery_count')->first();
        if (!$data) {
            return response()->json('$data');
        }
        return response()->json($data->redelivery_count);

        return Carbon::now('Asia/Jakarta')->format('ymd');
        $existRoute = Route::where([
            ['origin','=','KOTA SURABAYA'],
            ['destination_island','=','SUMATERA'],
            ['destination_city','=','KOTA MEDAN'],
            ['destination_district','=','MEDAN ']
        ])->first();
        if ($existRoute) {
            return 'exist';
        } else {
            return 'not exist';
        }
        return $existRoute;
        $result = Indonesia::search('jakarta')->allCities();
        return $result;
        $armada = collect(Fleet::all());
        $armada = $armada->where('slug','udara')->first()->only('id');
        return $armada;
        $route = Route::with('fleet')->get()->take(5)->makeHidden(['id','fleet_id']);
        $route = $route->map(function($q) {
            $fleet = $q->fleet->slug;
            $q->fleet_slug = $fleet;
            return $q;
        });
        return $route;
        $pickup = Pickup::select('picktime')->whereIn('id', [1,2,3,4])->get()->pluck('picktime');
        $pickup = collect($pickup)->toArray();
        $data = [];
        foreach ($pickup as $key => $value) {
            $data[] = Carbon::parse($value)->format('Y-m-d');
        }
        // return $data;
        // foreach ($pickup as $key => $value) {
        if (count(array_unique($data)) === 1) {
            return 'sama';
        }
        // }
        // return $pickup;

        // $allvalues = array('true', 'false', 'true');
        // if (count(array_unique($allvalues)) === 1 && end($allvalues) === 'true') {
        //     return 'sama';
        // }
        return 'berbeda';

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
        $items = Item::all();
        foreach ($items as $key => $value) {
            if ($value['unit_id'] == 1 || $value['unit_id'] == 2) {
                $weight = $value['unit_total'] * 0.001;
                Item::where('id',$value['id'])->update(['weight' => $weight]);
            }
            if ($value['unit_id'] == 3) {
                $volume = $value['unit_total'] * 1000;
                Item::where('id',$value['id'])->update(['volume' => $volume]);
            }
        }
        return response()->json($items);
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
    public function update(Request $request)
    {
        // $config = [
        //     'table' => 'proof_of_pickups',
        //     'length' => 14,
        //     'field' => 'number',
        //     'prefix' => 'POP'.Carbon::now('Asia/Jakarta')->format('ymd'),
        //     'reset_on_prefix_change' => true
        // ];
        // $collect = collect(ProofOfPickup::all());
        // $result = [];
        // foreach ($collect as $key => $value) {
        //     $data = ProofOfPickup::find($value['id']);
        //     $data->number = IdGenerator::generate($config);
        //     $result[] = $data->save();
        // }
        $collect = collect(User::all());
        $result = [];
        foreach ($collect as $key => $value) {
            $data = User::find($value['id']);
            if ($data['role_id'] == 2) {
                $customAlphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $generator = new RandomStringGenerator($customAlphabet);
                $generator->setAlphabet($customAlphabet);
                $refferal = $generator->generate(7);
                $data->refferal = $refferal;
                $result[] = $data->save();
            } else {
                $result[] = $data;
            }
        }
        return response()->json($result);
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
