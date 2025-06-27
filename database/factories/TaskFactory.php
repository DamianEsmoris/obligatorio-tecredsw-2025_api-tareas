<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTime();
        return [
            'title' => fake()->sentence(),
            'description' => fake()->text(),
            'completeness' => fake()->numberBetween(0,100),
            'author_id' => fake()->randomNumber(),
            'start_date' => $startDate,
            'due_date' => fake()->dateTimeBetween($startDate, '+30 years'),
        ];
    }
}
