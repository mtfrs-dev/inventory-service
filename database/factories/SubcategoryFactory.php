<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubcategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SUB-###')),
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'expected_items_count' => rand(5,20)
        ];
    }
}
