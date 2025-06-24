<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\HasAssigned;
use App\Models\Task;
use App\Models\User;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 9; $i++)
            User::factory()->create([ 'id' => $i ]);
        Category::factory(30)->create();
        Task::factory(20)->create();
        for ($i = 0; $i < 25; $i++)
            HasAssigned::factory()->create();
        Comment::factory(40)->create();
    }
}
