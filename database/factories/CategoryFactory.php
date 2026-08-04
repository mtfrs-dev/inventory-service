<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('CAT-###')),
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'expected_items_count' => rand(3,10)
        ];
    }
}
