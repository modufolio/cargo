<?php

namespace App\Repositories;

use App\Models\Tracking;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TrackingRepository
{
    protected $tracking;

    public function __construct(Tracking $tracking)
    {
        $this->tracking = $tracking;
    }

    /**
     * Get tracking by pickup
     *
     * @param array $data
     * @return Tracking
     */
    public function getTrackingByPickupRepo($data)
    {
        $data = $this->tracking->where('pickup_id', $data['pickupId'])->orderBy('created_at', 'DESC')->get();
        if (count($data) <= 0) {
            return 'Data tracking belum tersedia';
        }
        return $data;
    }

    /**
     * record tracking by pickup
     *
     * @param array $data
     * @return Tracking
     */
    public function recordTrackingByPickupRepo($data)
    {
        $tracking = new $this->tracking;
        $tracking->pickup_id = $data['pickupId'];
        $tracking->docs = $data['docs'];
        $tracking->status = $data['status'];
        $tracking->notes = $data['notes'];
        $tracking->picture = $data['picture'];
        $tracking->save();
        return $tracking;
    }

    /**
     * Upload tracking picture
     *
     * @param Request $request
     * @return array
     */
    public function uploadTrackingPicture($request)
    {
        $tracking               = $request->file('picture');
        $tracking_extension     = $tracking->getClientOriginalExtension();
        $timestamp              = Carbon::now('Asia/Jakarta')->timestamp;
        $file_name              = 'tracking'.$timestamp.'.'.$tracking_extension;
        Storage::disk('storage_tracking')->put($file_name,  File::get($tracking));
        $tracking_url           = '/upload/tracking/'.$file_name;
        return [
            'base_url' => env('APP_URL').'/public/storage',
            'path' => $tracking_url
        ];
    }
}