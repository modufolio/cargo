<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\User;
use App\Models\Pickup;
use Carbon\Carbon;

class ItemRepository
{
    protected $item;
    protected $pickup;

    public function __construct(Item $item, Pickup $pickup)
    {
        $this->item = $item;
        $this->pickup = $pickup;
    }

    /**
     * Get all item.
     *
     * @return Item $item
     */
    public function getAll()
    {
        return $this->item->get();
    }

    /**
     * Get item by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->item->where('id', $id)->get();
    }

    /**
     * Get item by pickup id
     *
     * @param $id
     * @return mixed
     */
    public function getByPickupId($id)
    {
        return $this->pickup->find($id)->items()->get();
    }

    /**
     * Update Item
     *
     * @param $data
     * @return Item
     */
    public function delete($id)
    {
        $item = $this->item->findOrFail($id);
        $item->delete();
        return $item;
    }

    /**
     * Save Item Address
     *
     * @param Pickup $data
     * @return Item
     */
    public function save($pickup, $items)
    {
        $pickup = $this->pickup->find($pickup['id']);

        $item = [];
        foreach ($items as $key => $value) {
            $item[] = $pickup->items()->create($value);
        }

        return $item;
    }

    /**
     * Update Item
     *
     * @param $data
     * @return Item
     */
    public function update($data, $id)
    {
        if ($data['is_primary']) {
            $this->updatePrimaryAddress($data['userId'], $id, false);
        }

        $item = $this->item->find($id);

        $item->is_primary = $data['is_primary'];
        $item->title = $data['title'];
        $item->receiptor = $data['receiptor'];
        $item->phone = $data['phone'];
        $item->province = $data['province'];
        $item->city = $data['city'];
        $item->district = $data['district'];
        $item->postal_code = $data['postal_code'];
        $item->street = $data['street'];
        $item->notes = $data['notes'];

        $item->save();

        return $item;
    }

    public function updatePrimaryAddress($userId, $item, $isPrimary)
    {
        $item = $this->item->where('user_id', $userId)->where('id', '!==', $item)->update(['is_primary' => $isPrimary]);
        return $item->fresh();
    }
}
