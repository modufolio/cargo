<?php
namespace App\Services;

use App\Repositories\BaseRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class BaseService {

    protected $baseRepository;

    public function __construct(BaseRepository $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }

    /**
     * Get base by name
     *
     * @param array $data
     * @return String
     */
    public function getBaseService($data)
    {
        $validator = Validator::make($data, [
            'value' => 'bail|required|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->baseRepository->getBaseByNameRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data base');
        }
        return $result;
    }
}
