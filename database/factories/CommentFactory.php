<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        $taskId = fake()->randomNumber(1,20);
        return [
            'task_id' => $taskId,
            'author_id' => fake()->randomNumber(),
            'body' => fake()->text(),
        ];
    }
}
