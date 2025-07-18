<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    private function validateData(Array $data) {
        $validation = Validator::make($data, [
            'body' => 'required',
            'author_id' => 'required|integer',
            'task_id' => 'integer'
        ]);
        $validationFailed = $validation->fails();
        return [$validationFailed, $validationFailed ? $validation->errors() : null];
    }

    public function Create(Request $request) {
        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $comment = new Comment();
        $comment->body = $request->post('body');
        $comment->task_id = $request->post('task_id');
        $comment->author_id = $request->post('author_id');
        $comment->save();

        Cache::forget('task_' . $comment->task_id);
        return $comment;
    }

    public function GetAll(Request $request) {
        return Comment::get();
    }

    public function Get(Request $request, int $id) {
        return Comment::findOrFail($id);
    }

    public function Modify(Request $request, int $id) {
        $comment = Comment::findOrFail($id);
        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $comment->body = $request->post('body');
        $comment->task_id = $request->post('task_id');
        $comment->author_id = $request->post('author_id');
        $comment->save();

        Cache::forget('task_' . $comment->task_id);
        return $comment;
    }

    public function Delete(Request $request, int $id) {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        Cache::forget('task_' . $comment->task_id);
        return response()->json([
            'deleted' => true
        ]);
    }
}
