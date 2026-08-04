<?php

namespace App\Actions\Item;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BulkUpdateItemSerialNumberAction
{
    public function handle(array $updates): Collection
    {
        return DB::transaction(function () use ($updates) {
            $ids   = array_column($updates, 'id');
            $items = Item::whereIn('id', $ids)->get()->keyBy('id');

            foreach ($updates as $update) {
                $item                = $items[$update['id']];
                $item->serial_number = $update['serial_number'];
                $item->save();
            }

            return Item::with('currentStatus')->whereIn('id', $ids)->get();
        });
    }
}
