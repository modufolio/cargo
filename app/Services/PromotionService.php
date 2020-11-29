<?php
namespace App\Services;

use App\Models\Promotion;

class PromotionService {
    public function handle($data) {
        $promotion = Promotion::create([
            'title'     => $data['title'],
            'content'   => $data['content'],
            'banner'    => $data['banner']
        ]);
        return $promotion;
    }

    public function create()
    {
        // logic here
    }

    public function update()
    {
        // logic here
    }

    public function destroy()
    {
        // logic here
    }
}
