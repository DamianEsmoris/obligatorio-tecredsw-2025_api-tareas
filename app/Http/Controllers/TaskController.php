<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Participates;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    private function validateData(Array $data) {
        $validation = Validator::make($data, [
            'title' => 'required',
            'description' => '',
            'author_id' => 'required|integer',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'completeness' => 'integer|min:0|max:100',
            'due_date' => 'date_format:Y-m-d H:i:s',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'participants' => 'nullable|array',
            'participants.*' => 'integer'
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
        $task->completeness = $request->post('completeness') || null;
        $task->author_id = $request->post('author_id');
        $task->start_date = $request->post('start_date');
        $task->due_date = $request->post('due_date');
        $task->save();

        if (($categories = $request->post('categories')) != null)
            $task->categories()->sync($categories);

        if (($participants = $request->post('participants')) != null)
            $task->participants()->createMany(
                array_map(fn (int $userId): array => [
                    'user_id' => $userId
                ], $participants
            ));

        Cache::tags('tasks')->flush();

        return $task->load('categories')->load('participants');
    }

    public function GetAll(Request $request) {
        $query = Task::query();
        $cacheKey = 'tasks';

        if ($request->has('author_id') && is_numeric($request->input('author_id'))) {
            $authorId = $request->input('author_id');
            $query->where('author_id', $authorId);
            $cacheKey .= '_author_id_' . $authorId;
        }

        if ($request->has('title')) {
            $searchTerm = $request->input('title');
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            $cacheKey .= '_title_' . $searchTerm;
        }

        if (Cache::has($cacheKey))
            return Cache::get($cacheKey);

        $result = $query->with('categories')->get();

        Cache::tags('tasks')->put($cacheKey, $result, 180);

        return $result;
    }

    public function Get(Request $request, int $id) {
        $key = 'task_' . $id;
        if (Cache::has($key))
            return Cache::get($key);
        $result = Task::with('comments')
            ->with('categories')
            ->with('participants')
            ->findOrFail($id);
        Cache::put($key, $result, 180);
        return $result;
    }



    public function Modify(Request $request, int $id) {
        $task = Task::findOrFail($id);

        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $task->title = $request->post('title');
        $task->description = $request->post('description');
        $task->completeness = $request->post('completeness') || null;
        $task->author_id = $request->post('author_id');
        $task->start_date = $request->post('start_date');
        $task->due_date = $request->post('due_date');
        $task->save();

        if (($categories = $request->post('categories')) != null)
            $task->categories()->sync($categories);

        if (($participants = $request->post('participants')) != null) {
            $existingParticipants = $task->participants()->pluck('user_id')->toArray();

            $toDelete = array_diff($existingParticipants, $participants);
            $task->participants()->whereIn('user_id', $toDelete)->delete();

            $toInsert = array_diff($participants, $existingParticipants);
            $task->participants()->createMany(
                array_map(fn (int $userId): array => [
                    'user_id' => $userId
                ], $toInsert
            ));
        }

        Cache::tags('tasks')->flush();
        Cache::forget('task_' . $id);

        return $task->load('categories');
    }

    public function Delete(Request $request, int $id) {
        $task = Task::findOrFail($id);
        $task->categories()->detach();
        Participates::where('task_id', $id)->delete();
        Comment::where('task_id', $id)->delete();
        $task->delete();

        Cache::tags('tasks')->flush();

        return response()->json([
            'deleted' => true
        ]);
    }
}
