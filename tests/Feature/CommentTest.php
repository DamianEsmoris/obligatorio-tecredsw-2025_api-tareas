<?php

namespace Tests\Feature;

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommentTest extends TestCase
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
        $accessToken = $this->getAccessToken();
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ]);
    }

    private function getCommentStructure() : Array
    {
        return [
            'body',
            'author_id',
            'task_id',
        ];
    }

    private function getExampleComment() : Array
    {
        return [
            'body' => '.',
            'author_id' => 1,
            'task_id' => 1,
        ];
    }

    // GET: /api/comments

    public function test_getAllComments(): void
    {
        $response = $this->getJson('/api/comment');
        $response->assertStatus(200);
        $response->assertJsonStructure(['*' => $this->getCommentStructure()]);
    }

    public function test_getExistentComment(): void
    {
        $response = $this->getJson('/api/comment/1');
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getCommentStructure());
    }

    public function test_getNoneExistentComment(): void
    {
        $response = $this->getJson('/api/comment/999');
        $response->assertStatus(404);
    }

    // POST: /api/comment

    public function test_createCommentNoAuth(): void
    {
        $response = $this->postJson('/api/comment', $this->getExampleComment());
        $response->assertStatus(401);
    }

    public function test_createCommentAuth(): void
    {
        $commentData = $this->getExampleComment();
        $response = $this->requestWithToken()->postJson('/api/comment', $commentData);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->getCommentStructure());
        $this->assertDatabaseHas('comments', [
            'body' => $commentData['body'],
            'task_id' => $commentData['task_id'],
        ]);
    }

    // PUT: /api/comment/{id}

    public function test_updateExistentCommentNoAuth(): void
    {
        $response = $this->putJson('/api/comment/1', $this->getExampleComment());
        $response->assertStatus(401);
    }

    public function test_updateExistentCommentAuth(): void
    {
        $updatedData = $this->getExampleComment();
        $response = $this->requestWithToken()->putJson('/api/comment/1', $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => 1,
            'body' => $updatedData['body'],
        ]);
    }

    public function test_updateNoneExistentCommentAuth(): void
    {
        $response = $this->requestWithToken()->putJson('/api/comment/999', ['body' => '...']);
        $response->assertStatus(404);
    }

    // DELETE: /api/comment/{id}

    public function test_deleteExistentCommentNoAuth(): void
    {
        $response = $this->deleteJson('/api/comment/2');
        $response->assertStatus(401);
    }

    public function test_deleteExistentCommentAuth(): void
    {
        $response = $this->requestWithToken()->deleteJson('/api/comment/2');
        $response->assertStatus(200);
        $this->assertSoftDeleted('comments', ['id' => 2]);
    }

    public function test_deleteNoneExistentCommentAuth(): void
    {
        $response = $this->requestWithToken()->deleteJson('/api/comment/999');
        $response->assertStatus(404);
    }
}
