<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\User;
use App\Models\Pickup;
use Carbon\Carbon;
use InvalidArgumentException;
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
     * @param array $data
     * @return Item
     */
    public function updateItemRepo($data = [])
    {
        $item = $this->item->find($data['itemId']);

        if (!$item) {
            throw new InvalidArgumentException('Item tidak ditemukan');
        }

        $item->name = $data['name'];
        $item->unit_count = $data['count'];
        $item->unit_total = $data['total'];
        $item->service_id = $data['serviceId'] ?? null;
        $item->save();

        return $item;
    }
}
