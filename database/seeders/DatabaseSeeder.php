<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Category::factory(30)->create();
        Task::factory(20)->create();
    }
}
