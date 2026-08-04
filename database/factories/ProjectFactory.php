<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    private static int $counter = 1;

    public function definition(): array
    {
        $year = '2026';
        return [
            'name'              => fake()->sentence(),
            'year'              => $year,
            'code'              => 'P' . self::$counter++ . '-' . $year,
        ];
    }
}
