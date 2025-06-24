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
<<<<<<< Updated upstream
            'author_id' => 'required|integer',
            'start_date' => 'datetime',
            'due_date' => 'datetime',
=======
            'author_id' => 'required|integer|exists:users,id',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'due_date' => 'date_format:Y-m-d H:i:s',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id'
>>>>>>> Stashed changes
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

        return $task;
    }

    public function GetAll(Request $request) {
        return Task::get();
    }

    public function Get(Request $request, int $id) {
        return Task::findOrFail($id);
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

        return $task;
    }

    public function Delete(Request $request, int $id) {
        Task::findOrFail($id)->delete();
        return response()->json([
            'deleted' => true
        ]);
    }
}
