<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTime();
        $authorId = fake()->randomNumber(1,8);
        return [
            'title' => fake()->sentence(),
            'description' => fake()->text(),
            'author_id' => $authorId,
            'start_date' => $startDate,
            'due_date' => fake()->dateTimeBetween($startDate, '+30 years'),
        ];
    }
}
