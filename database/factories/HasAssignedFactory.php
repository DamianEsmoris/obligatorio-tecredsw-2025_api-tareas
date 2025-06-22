<?php

namespace Database\Factories;

use App\Models\HasAssigned;
use Illuminate\Database\Eloquent\Factories\Factory;

class HasAssignedFactory extends Factory
{
    public function definition(): array
    {
        $taskId = $categoryId = null;
        do {
            $taskId = fake()->numberBetween(1,20);
            $categoryId = fake()->numberBetween(1,30);
        } while (HasAssigned::where('task_id', $taskId)
            ->where('category_id', $categoryId)->exists());

        return [
            'task_id' => $taskId,
            'category_id' => $categoryId
        ];
    }
}
