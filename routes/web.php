<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
// MODEL
use App\Models\User;
use App\Models\Role;
use App\Models\Fleet;
use App\Models\Route as RouteModel;
use App\Models\Promo;
use App\Models\Driver;
use App\Models\Branch;
use App\Models\Debtor;
use App\Models\Item;
use App\Models\Pickup;
use App\Models\PickupPlan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/flush-file-cache', function() {
    Cache::store("file")->flush();
});

Route::get('/test', function() {
    $debtor = Debtor::find(1);
    $a = $debtor->replicate();
    $a->temporary = true;
    $debtor = $a->toArray();
    $debtor = Debtor::firstOrCreate($debtor);
    return $debtor;
    return $pickup['receiver']['city'];
    $branch = Branch::whereHas('pickups', function($q) {
        $q->where('id', 7);
    })->first();
    if (!$branch) {
        return response()->json('nulls');
    }
    return $branch['id'];
});

Route::get('/test-email', function()
{
	$beautymail = app()->make(Snowfire\Beautymail\Beautymail::class);
	$beautymail->send('emails.verify-email', [], function($message)
	{
		$message
			->from('ival@papandayan.com')
			->to('ivalrival95@gmail.com', 'Ival')
			->subject('Welcome!');
	});

});
