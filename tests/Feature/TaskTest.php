<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getBasicTaskStructure() : Array
    {
        return [
            'id',
            'title',
            'description',
            'author_id',
            'created_at',
            'updated_at'
        ];
    }

    private function getExampleTask() : Array
    {
        return [
            'title' => '.',
            'description' => '.',
            'author_id' => '1',
        ];
    }

    public function test_getAllTasks(): void
    {
        $response = $this->get('/api/task');
        $response->assertStatus(200);
         $response->assertJsonStructure([
            '*' => $this->getBasicTaskStructure()
        ]);
    }

    public function test_getExistentTask(): void
    {
        $response = $this->get('/api/task/1');
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getBasicTaskStructure());
    }

    public function test_getNoneExistentTask(): void
    {
        $response = $this->get('/api/task/999');
        $response->assertStatus(404);
    }

    public function test_createTask(): void
    {
        $response = $this->post('/api/task', $this->getExampleTask());
        $response->assertStatus(201);
        $response->assertJsonStructure($this->getBasicTaskStructure());
        $data = $response->json();
        $this->assertDatabaseHas('tasks', [
            'id' => $data['id']
        ]);
    }

    public function test_updateExistentTask(): void
    {
        $response = $this->put('/api/task/1', $this->getExampleTask());
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getBasicTaskStructure());
        $data = $response->json();
        $this->assertDatabaseHas('tasks', [
            ...$this->getExampleTask(),
            'id' => $data['id']
        ]);
    }

    public function test_updateNoneExistentTask(): void
    {
        $response = $this->put('/api/task/999', $this->getExampleTask());
        $response->assertStatus(404);
        $this->assertDatabaseMissing('tasks', [
            'id' => 999,
            'deleted_at' => null
        ]);
    }

    public function test_deleteExistentTask(): void
    {
        $response = $this->delete('/api/task/2');
        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', [
            'id' => 2,
            'deleted_at' => null
        ]);
    }

    public function test_deleteNoneExistentTask(): void
    {
        $response = $this->delete('/api/task/999');
        $response->assertStatus(404);
        $this->assertDatabaseMissing('tasks', [
            'id' => 999,
            'deleted_at' => null
        ]);
    }
}
