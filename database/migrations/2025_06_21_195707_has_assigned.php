<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('have_assigned', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('category_id');
            $table->foreign('task_id')
                ->references('id')
                ->on('tasks');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories');
            $table->unique(['task_id', 'category_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('have_assigned');
    }
};
