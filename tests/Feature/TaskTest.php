<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;
    private $accessToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getAccessToken($retried = false)
    {
        if ($this->accessToken != null)
            return $this->accessToken;
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(config('services.api_oauth.login_url'), [
            "grant_type" => "password",
            "client_id" => config('services.api_oauth.client_id'),
            "client_secret" => config('services.api_oauth.client_secret'),
            "username" => "pruebas@api-tareas",
            "password" => "%.NU*GTxFL*(~.=K]=\H"
        ]);

        if (!$response->successful() && !$retried) {
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(config('services.api_oauth.register_url'), [
                "name" => "pruebas-api-tareas",
                "email" => "pruebas@api-tareas",
                "password" => "%.NU*GTxFL*(~.=K]=\H",
                "password_confirmation" => "%.NU*GTxFL*(~.=K]=\H"
            ]);
            return $this->getAccessToken(true);
        }
        $this->accessToken = $response->json('access_token');
        return $this->accessToken;
    }

    private function requestWithToken()
    {
        $this->accessToken = $this->getAccessToken();
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ]);
    }

    private function getTaskStructureWithCategories() : Array
    {
        return [
            'id',
            'title',
            'description',
            'completeness',
            'author_id',
            'categories',
            'created_at',
            'updated_at'
        ];
    }

    private function getExampleTask() : Array
    {
        return [
            'title' => '.',
            'description' => '.',
            'completeness' => 1,
            'author_id' => 1,
        ];
    }

    // GET: /api/task

    public function test_getAllTasks(): void
    {
        $response = $this->getJson('/api/task');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => $this->getTaskStructureWithCategories()
        ]);
    }

    public function test_getExistentTask(): void
    {
        $response = $this->getJson('/api/task/1');
        $response->assertStatus(200);
        $taskResponseStructure = $this->getTaskStructureWithCategories();
        array_push($taskResponseStructure, 'comments');
        array_push($taskResponseStructure, 'participants');
        $response->assertJsonStructure($taskResponseStructure);
    }

    public function test_getNoneExistentTask(): void
    {
        $response = $this->getJson('/api/task/999');
        $response->assertStatus(404);
    }

    // POST: /api/task

        public function test_createTaskNoAuth(): void
    {
        $response = $this->postJson('/api/task', $this->getExampleTask());
        $response->assertStatus(401);
    }

    public function test_createTaskAuth(): void
    {
        $taskData = $this->getExampleTask();
        $response = $this->requestWithToken()->postJson('/api/task', $taskData);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->getTaskStructureWithCategories());

        $data = $response->json();
        $this->assertDatabaseHas('tasks', [
            'id' => $data['id'],
            'title' => $taskData['title'],
        ]);
    }

    public function test_createWithCategoriesTaskNoAuth(): void
    {
        $task = $this->getExampleTask();
        $task['categories'] = [1, 2, 3];
        $response = $this->postJson('/api/task', $task);
        $response->assertStatus(401);
    }

    public function test_createWithCategoriesTaskAuth(): void
    {
        $taskData = $this->getExampleTask();
        $taskData['categories'] = [1, 2, 3];

        $response = $this->requestWithToken()->postJson('/api/task', $taskData);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->getTaskStructureWithCategories());

        $data = $response->json();
        $this->assertDatabaseHas('tasks', ['id' => $data['id']]);

        foreach ($taskData['categories'] as $categoryId) {
            $this->assertDatabaseHas('have_assigned', [
                'task_id' => $data['id'],
                'category_id' => $categoryId,
            ]);
        }
    }

    public function test_createWithCategoriesAndParticipantsTaskAuth(): void
    {
        $taskData = $this->getExampleTask();
        $taskData['categories'] = [1, 2, 3];
        $taskData['participants'] = [7, 5, 3];

        $response = $this->requestWithToken()->postJson('/api/task', $taskData);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->getTaskStructureWithCategories());

        $data = $response->json();
        $this->assertDatabaseHas('tasks', ['id' => $data['id']]);

        foreach ($taskData['categories'] as $categoryId)
            $this->assertDatabaseHas('have_assigned', [
                'task_id' => $data['id'],
                'category_id' => $categoryId,
            ]);

        foreach ($taskData['participants'] as $participantId)
            $this->assertDatabaseHas('participate', [
                'task_id' => $data['id'],
                'user_id' => $participantId,
            ]);
    }

    // PUT: /api/task/{id}

    public function test_updateExistentTaskNoAuth(): void
    {
        $response = $this->putJson('/api/task/1', $this->getExampleTask());
        $response->assertStatus(401);
    }

    public function test_updateExistentTaskAuth(): void
    {
        $taskData = $this->getExampleTask();
        $response = $this->requestWithToken()->putJson('/api/task/1', $taskData);
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getTaskStructureWithCategories());
        $this->assertDatabaseHas('tasks', [
            'id' => 1,
            'title' => $taskData['title']
        ]);
    }

    public function test_updateExistentTaskChangingCategoriesNoAuth(): void
    {
        $task = $this->getExampleTask();
        $task['categories'] = [2, 3, 5, 7];
        $response = $this->putJson('/api/task/1', $task);
        $response->assertStatus(401);
    }

    public function test_updateExistentTaskChangingCategoriesAuth(): void
    {
        $taskData = $this->getExampleTask();
        $taskData['categories'] = [2, 4];
        $response = $this->requestWithToken()->putJson('/api/task/1', $taskData);
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getTaskStructureWithCategories());
        $this->assertDatabaseHas('tasks', ['id' => 1, 'title' => $taskData['title']]);
        foreach ($taskData['categories'] as $categoryId)
            $this->assertDatabaseHas('have_assigned', [
                'task_id' => 1,
                'category_id' => $categoryId,
                'deleted_at' => null
            ]);
    }

    public function test_updateExistentTaskChangingCategoriesAndParticipantsAuth(): void
    {
        $taskData = $this->getExampleTask();
        $taskData['categories'] = [2, 4];
        $taskData['participants'] = [32, 64];
        $response = $this->requestWithToken()->putJson('/api/task/1', $taskData);
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getTaskStructureWithCategories());
        $this->assertDatabaseHas('tasks', ['id' => 1, 'title' => $taskData['title']]);
        foreach ($taskData['categories'] as $categoryId)
            $this->assertDatabaseHas('have_assigned', [
                'task_id' => 1,
                'category_id' => $categoryId,
                'deleted_at' => null
            ]);

        foreach ($taskData['participants'] as $participantId)
            $this->assertDatabaseHas('participate', [
                'task_id' => 1,
                'user_id' => $participantId,
                'deleted_at' => null
            ]);
    }

    public function test_updateNoneExistentTaskNoAuth(): void
    {
        $response = $this->putJson('/api/task/999', $this->getExampleTask());
        $response->assertStatus(401);
    }

    public function test_updateNoneExistentTaskAuth(): void
    {
        $response = $this->requestWithToken()->putJson('/api/task/999', $this->getExampleTask());
        $response->assertStatus(404);
    }

    // DELETE: /api/task/{id}

    public function test_deleteExistentTaskNoAuth(): void
    {
        $response = $this->deleteJson('/api/task/2');
        $response->assertStatus(401);
    }

    public function test_deleteExistentTaskAuth(): void
    {
        $response = $this->requestWithToken()->deleteJson('/api/task/2');
        $response->assertStatus(200);

        $this->assertSoftDeleted('tasks', [
            'id' => 2,
        ]);
    }

    public function test_deleteNoneExistentTaskNoAuth(): void
    {
        $response = $this->deleteJson('/api/task/999');
        $response->assertStatus(401);
    }

    public function test_deleteNoneExistentTaskAuth(): void
    {
        $response = $this->requestWithToken()->deleteJson('/api/task/999');
        $response->assertStatus(404);
    }
}
