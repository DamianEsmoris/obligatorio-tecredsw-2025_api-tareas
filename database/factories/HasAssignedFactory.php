<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HasAssignedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => fake()->numberBetween(1,20),
            'category_id' => fake()->numberBetween(1,30),
        ];
    }
}
