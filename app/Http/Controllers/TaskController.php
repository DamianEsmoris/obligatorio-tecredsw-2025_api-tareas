<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    private function validateData(Array $data) {
        $validation = Validator::make($data, [
            'title' => 'required',
            'description' => '',
            'author_id' => 'required|integer',
            'start_date' => 'datetime',
            'due_date' => 'datetime',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id'
        ]);
        $validationFailed = $validation->fails();
        return [$validationFailed, $validationFailed ? $validation->errors() : null];
    }

    public function Create(Request $request) {
        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $task = new Task();
        $task->title = $request->post('title');
        $task->description = $request->post('description');
        $task->author_id = $request->post('author_id');
        $task->start_date = $request->post('start_date');
        $task->due_date = $request->post('due_date');
        $task->save();

        if (($categories = $request->post('categories') != null))
            $task->categories()->sync($categories);

        return $task->load('categories');
    }

    public function GetAll(Request $request) {
        return Task::with('categories')->get();
    }

    public function Get(Request $request, int $id) {
        return Task::with('comments')->with('categories')->findOrFail($id);
    }

    public function Modify(Request $request, int $id) {
        $task = Task::findOrFail($id);

        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $task->title = $request->post('title');
        $task->description = $request->post('description');
        $task->author_id = $request->post('author_id');
        $task->start_date = $request->post('start_date');
        $task->due_date = $request->post('due_date');
        $task->save();

        if (($categories = $request->post('categories') != null))
            $task->categories()->sync($categories);

        return $task->load('categories');
    }

    public function Delete(Request $request, int $id) {
        $task = Task::findOrFail($id);
        $task->categories()->detach();
        $task->delete();
        return response()->json([
            'deleted' => true
        ]);
    }
}
