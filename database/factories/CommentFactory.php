<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => fake()->numberBetween(1,20),
            'author_id' => fake()->randomDigit(),
            'body' => fake()->text(),
        ];
    }
}
