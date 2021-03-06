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
     * Save Item
     *
     * @param Pickup $data
     * @return Item
     */
    public function save($pickup, $items)
    {
        $pickup = $this->pickup->find($pickup['id']);

        if (!$pickup) {
            throw new InvalidArgumentException('Pickup tidak ditemukan, gagal menyimpan item');
        }

        $item = [];
        foreach ($items as $key => $value) {
            // $item[] = $pickup->items()->create($value);
            $data = new $this->item;
            $data->pickup_id = $pickup['id'];
            $data->service_id = $value['service_id'] ?? NULL;
            $data->name = $value['name'];
            $data->weight = $value['weight'];
            $data->volume = $value['volume'];
            $data->unit = $value['unit'];
            $data->unit_count = $value['unit_count'];
            $data->type = $value['type'];
            $data->save();
            $item[] = $data;
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
        // $item->unit_total = $data['total'];
        $item->weight = $data['weight'];
        $item->volume = $data['volume'];
        $item->type = $data['type'];
        $item->service_id = $data['serviceId'] ?? null;
        // $item->unit_id = $data['unitId'];
        $item->save();

        return $item;
    }

    /**
     * fetch Item by pickup
     *
     * @param array $data
     * @return Item
     */
    public function fetchItemByPickupRepo($data = [])
    {
        $item = $this->item->with('service')->where('pickup_id', $data['pickupId'])->get();

        if (!$item) {
            throw new InvalidArgumentException('Item tidak ditemukan');
        }

        return $item;
    }

    /**
     * Update Pickup Items
     *
     * @param array $data
     * @return Item
     */
    public function updatePickupItemsRepo($data = [])
    {
        foreach ($data['items'] as $key => $value) {
            $item = $this->item->find($value['id']);
            if (!$item) {
                throw new InvalidArgumentException('Item tidak ditemukan');
            }
            $item->name = $value['name'];
            $item->unit_count = $value['unit_count'];
            $item->weight = $value['weight'];
            $item->type = $value['type'];
            $item->volume = $value['volume'];
            $item->unit = $value['unit'];
            $item->price = $value['price'] ?? null;
            $item->service_id = $value['service_id'] ?? null;
            $item->save();
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Update Item on drop order
     *
     * @param Pickup $data
     * @return Item
     */
    public function updateItemDrop($pickup, $items)
    {
        // DELETE OLD ITEM
        $this->item->where('pickup_id', $pickup['id'])->delete();

        // SAVE NEW ITEM
        $pickup = $this->pickup->find($pickup['id']);
        $item = [];
        foreach ($items as $key => $value) {
            $item[] = $pickup->items()->create($value);
            // $data = new $this->item;
            // $data->service_id = $value['service_id'] ?? NULL;
            // $data->name = $value['name'];
            // $data->weight = $value['weight'];
            // $data->volume = $value['volume'];
            // $data->save();
            // $item[] = $data;
        }
        return $item;
    }
}
