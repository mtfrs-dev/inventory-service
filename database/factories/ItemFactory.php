<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subcategory_id'    => null,
            'serial_number'     => strtoupper(fake()->unique()->bothify('SN-????-########')),
            'code'              => strtoupper(fake()->unique()->bothify('ITM#########')),
            'qr_code'           => null,
            'current_status_id' => Status::inRandomOrder()->first()?->id,
        ];
    }

    public function withQrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'qr_code' => "qr-codes/{$this->faker->uuid()}.png",
        ]);
    }
}
