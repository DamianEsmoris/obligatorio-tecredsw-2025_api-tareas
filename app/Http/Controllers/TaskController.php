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
            'description' => 'required',
            'status' => 'required',
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
        $task->status = $request->post('status');
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
        $task->status = $request->post('status');
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
