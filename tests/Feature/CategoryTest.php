<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    private $accessToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getBasicCategoryStructure() : Array
    {
        return [
            'id',
            'name',
        ];
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
        $accessToken = $this->getAccessToken();
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ]);
    }

    private function getCategoryStructureWithTasks() : Array
    {
        return [
            'id',
            'name',
            'assigned_to'
        ];
    }

    private function getExampleCategory() : Array
    {
        return [
            'name' => 'done',
        ];
    }

    // GET: /api/category

    public function test_getAllCategories(): void
    {
        $response = $this->getJson('/api/category');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => $this->getCategoryStructureWithTasks()
        ]);
    }

    public function test_getExistentCategory(): void
    {
        $response = $this->getJson('/api/category/1');
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getCategoryStructureWithTasks());
    }

    public function test_getNoneExistentCategory(): void
    {
        $response = $this->getJson('/api/category/999');
        $response->assertStatus(404);
    }

    // POST: /api/category

    public function test_createCategoryNoAuth(): void
    {
        $response = $this->postJson('/api/category', $this->getExampleCategory());
        $response->assertStatus(401);
    }

    public function test_createCategoryAuth(): void
    {
        $categoryData = $this->getExampleCategory();
        $response = $this->requestWithToken()->postJson('/api/category', $categoryData);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->getBasicCategoryStructure());

        $data = $response->json();
        $this->assertDatabaseHas('categories', [
            'id' => $data['id'],
            'name' => $categoryData['name'],
        ]);
    }

    // PUT: /api/category/{id}

    public function test_updateExistentCategoryNoAuth(): void
    {
        $response = $this->putJson('/api/category/1', $this->getExampleCategory());
        $response->assertStatus(401);
    }

    public function test_updateExistentCategoryAuth(): void
    {
        $updatedData = ['name' => 'Updated Category Name'];
        $response = $this->requestWithToken()->putJson('/api/category/1', $updatedData);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->getBasicCategoryStructure());
        $this->assertDatabaseHas('categories', [
            'id' => 1,
            'name' => 'Updated Category Name',
        ]);
    }

    public function test_updateNoneExistentCategoryNoAuth(): void
    {
        $response = $this->putJson('/api/category/999', $this->getExampleCategory());
        $response->assertStatus(401);
    }

    public function test_updateNoneExistentCategoryAuth(): void
    {
        $response = $this->requestWithToken()->putJson('/api/category/999', $this->getExampleCategory());
        $response->assertStatus(404);
    }

    // DELETE: /api/category/{id}

    public function test_deleteExistentCategoryNoAuth(): void
    {
        $response = $this->deleteJson('/api/category/2');
        $response->assertStatus(401);
    }

    public function test_deleteExistentCategoryAuth(): void
    {
        $response = $this->requestWithToken()->deleteJson('/api/category/2');
        $response->assertStatus(200);
        $this->assertSoftDeleted('categories', [
            'id' => 2,
        ]);
    }

    public function test_deleteNoneExistentCategoryNoAuth(): void
    {
        $response = $this->deleteJson('/api/category/999');
        $response->assertStatus(401);
    }

    public function test_deleteNoneExistentCategoryAuth(): void
    {
        $response = $this->requestWithToken()->deleteJson('/api/category/999');
        $response->assertStatus(404);
    }

}
