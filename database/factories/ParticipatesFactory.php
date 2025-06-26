<?php

namespace Database\Factories;

use App\Models\Participates;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipatesFactory extends Factory
{
    public function definition(): array
    {
        $taskId = $userId = null;
        do {
            $taskId = fake()->numberBetween(1,20);
            $userId = fake()->numberBetween();
        } while (Participates::where('task_id', $taskId)
            ->where('user_id', $userId)->exists());

        return [
            'task_id' => $taskId,
            'user_id' => $userId
        ];
    }
}
