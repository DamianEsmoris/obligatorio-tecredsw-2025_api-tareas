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
            'author_id' => fake()->randomDigit(),
            'start_date' => $startDate,
            'due_date' => fake()->dateTimeBetween($startDate, '+30 years'),
        ];
    }
}
