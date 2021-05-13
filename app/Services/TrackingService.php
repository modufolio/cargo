<?php
namespace App\Services;

use App\Repositories\TrackingRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class TrackingService {

    protected $trackingRepository;

    public function __construct(TrackingRepository $trackingRepository)
    {
        $this->trackingRepository = $trackingRepository;
    }

    /**
     * Get tracking by pickup
     *
     * @param array $data
     * @return String
     */
    public function getTrackingByPickupService($data)
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->trackingRepository->getTrackingByPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data tracking');
        }
        return $result;
    }

    /**
     * record tracking by pickup
     *
     * @param array $data
     * @return String
     */
    public function recordTrackingByPickupService($data)
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
            'docs' => 'bail|required',
            'status' => 'bail|required',
            'notes' => 'bail|required',
            'picture' => 'bail',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $result = $this->trackingRepository->recordTrackingByPickupRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data tracking');
        }
        DB::commit();
        return $result;
    }

    /**
     * upload tracking picture
     */
    public function uploadTrackingPictureService($request)
    {
        $validator = Validator::make($request->all(), [
            'picture' => 'required|file|max:1024|mimes:jpg,png,jpeg',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $result = $this->trackingRepository->uploadTrackingPicture($request);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }
}
