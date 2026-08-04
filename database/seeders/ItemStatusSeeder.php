<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemStatusSeeder extends Seeder
{
    public function run(): void
    {
        Item::with('currentStatus')
            ->get()
            ->each(function ($item) {
                ItemStatus::create([
                    'item_id' => $item->id,
                    'status_id' => $item->current_status_id,
                    'previous_status_id' => $item->current_status_id,
                    'updated_by' => User::inRandomOrder()->first()?->id,
                    'is_active' => true,
                    'reason' => 'Initial status',
                    'effective_at' => now(),
                ]);
            });
    }
}
