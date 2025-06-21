<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->name(),
            'description' => fake()->word(),
            'status' => fake()->word(),
        ];
    }
}
