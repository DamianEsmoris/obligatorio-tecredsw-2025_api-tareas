<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Participates;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Catch_;

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
        $task->completeness = $request->post('completeness') ?? null;
        $task->author_id = $request->post('author_id');
        $task->start_date = $request->post('start_date');
        $task->due_date = $request->post('due_date');
        try {
            DB::beginTransaction();
            $task->save();

            if (($categories = $request->post('categories')) != null)
                $task->categories()->sync($categories);

            if (($participants = $request->post('participants')) != null)
                $task->participants()->createMany(
                    array_map(fn (int $userId): array => [
                        'user_id' => $userId
                    ], $participants
                ));

            $taskData = $task->toArray();
            $taskData['task_id'] = $taskData['id'];
            unset($taskData['id']);

            Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(config('services.api_history.task_url'),
                $taskData
            );

            DB::commit();
            Cache::tags('tasks')->flush();

            return $task->load('categories')->load('participants');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Task deletion failed',
            ], 500);
        }
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

        if ($request->has('completeness') && is_numeric($request->input('completeness'))) {
            $authorId = $request->input('completeness');
            $query->where('completeness', $authorId);
            $cacheKey .= '_completenees_eq_' . $authorId;
        }

        if ($request->has('comp_min') && is_numeric($request->input('comp_min'))) {
            $minimun = $request->input('comp_min');
            $query->whereRaw('completeness >= ?', [$minimun]);
            $cacheKey .= '_completenees_min_' . $minimun;
        }

        if ($request->has('comp_max') && is_numeric($request->input('comp_max'))) {
            $maximum = $request->input('comp_max');
            $query->whereRaw('completeness <= ?', [$maximum]);
            $cacheKey .= '_completenees_max_' . $maximum;
        }

    if ($request->has('participant_id') && is_numeric($request->input('participant_id'))) {
        $participantUserId = intval($request->input('participant_id'));
        $query->whereHas('participants', fn ($query) =>
            $query->where('user_id', $participantUserId)
        );
        $cacheKey .= '_participant_id_' . $participantUserId;
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
        Cache::tags('tasks')->put($key, $result, 180);
        return $result;
    }



    public function Modify(Request $request, int $id) {
        $task = Task::findOrFail($id);

        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $task->title = $request->post('title');
        $task->description = $request->post('description');
        $task->completeness = $request->post('completeness') ?? null;
        $task->author_id = $request->post('author_id');
        $task->start_date = $request->post('start_date');
        $task->due_date = $request->post('due_date');

         DB::beginTransaction();

        try {
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

            DB::commit();
            Cache::tags('tasks')->flush();
            Cache::forget('task_' . $id);
            return $task->load('categories');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Task modification failed',
            ], 500);
        }
    }

    public function Delete(Request $request, int $id) {
        $task = Task::findOrFail($id);
        try {
            DB::beginTransaction();
            $task->categories()->detach();
            Participates::where('task_id', $id)->delete();
            Comment::where('task_id', $id)->delete();
            $task->delete();

            DB::commit();
            Cache::tags('tasks')->flush();

            return response()->json([
                'deleted' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Task deletion failed',
            ], 500);
        }
    }
}
