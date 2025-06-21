<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getBasicCategoryStructure() : Array
    {
        return [
            'id',
            'name'
        ];
    }

    private function getExampleCategory() : Array
    {
        return [
            'name' => 'done',
        ];
    }

    public function test_getAllCategorys(): void
    {
        $response = $this->get('/api/category');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => $this->getBasicCategoryStructure()
        ]);
    }

    public function test_getExistentCategory(): void
    {
        $response = $this->get('/api/category/1');
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getBasicCategoryStructure());
    }

    public function test_getNoneExistentCategory(): void
    {
        $response = $this->get('/api/category/999');
        $response->assertStatus(404);
    }

    public function test_createCategory(): void
    {
        $response = $this->post('/api/category', $this->getExampleCategory());
        $response->assertStatus(201);
        $response->assertJsonStructure($this->getBasicCategoryStructure());
        $data = $response->json();
        $this->assertDatabaseHas('categories', [
            'id' => $data['id']
        ]);
    }

    public function test_updateExistentCategory(): void
    {
        $response = $this->put('/api/category/1', $this->getExampleCategory());
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getBasicCategoryStructure());
        $data = $response->json();
        $this->assertDatabaseHas('categories', [
            ...$this->getExampleCategory(),
            'id' => $data['id']
        ]);
    }

    public function test_updateNoneExistentCategory(): void
    {
        $response = $this->put('/api/category/999', $this->getExampleCategory());
        $response->assertStatus(404);
        $this->assertDatabaseMissing('categories', [
            'id' => 999,
            'deleted_at' => null
        ]);
    }

    public function test_deleteExistentCategory(): void
    {
        $response = $this->delete('/api/category/2');
        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', [
            'id' => 2,
            'deleted_at' => null
        ]);
    }

    public function test_deleteNoneExistentCategory(): void
    {
        $response = $this->delete('/api/category/999');
        $response->assertStatus(404);
        $this->assertDatabaseMissing('categories', [
            'id' => 999,
            'deleted_at' => null
        ]);
    }
}
