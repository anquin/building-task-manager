<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     @OA\Property(property="id", type="string", example="2d617157-e8bf-430e-8cbf-671ef680320a"),
 *     @OA\Property(property="summary", type="string", example="Brief Task Description"),
 *     @OA\Property(property="creator", type="string", example="3ed50d5b-7715-4c4d-b5e2-624786e98aac"),
 *     @OA\Property(property="assignee", type="string", example="3ed50d5b-7715-4c4d-b5e2-624786e98aac"),
 *     @OA\Property(property="status", type="string", example="open"),
 *     @OA\Property(property="comments", type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="user_id", type="string", example="uuid"),
 *             @OA\Property(property="text", type="string", example="Nice job!"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="List all tasks",
     *     tags={"Tasks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="assignee",
     *         in="query",
     *         description="Filter by assignee user ID",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for filtering (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for filtering (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->has('assignee')) {
            $query->where('assignee', $request->input('assignee'));
        }

        if ($request->has(['date_from', 'date_to'])) {
            $query->whereBetween('created_at', [
                $request->input('date_from'),
                $request->input('date_to'),
            ]);
        } elseif ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        } elseif ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $tasks = $query->with(['creator', 'assignee'])->get();
        return TaskResource::collection($tasks);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"building_id", "summary"},
     *             @OA\Property(property="building_id", type="string", example="uuid"),
     *             @OA\Property(property="summary", type="string", example="Fix the lights in hallway"),
     *             @OA\Property(property="assignee", type="string", example="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();

        Log::info(['creator' => Auth::id()]);
        $task = Task::create([
            'id' => (string) Str::uuid(),
            'building_id' => $validated['building_id'],
            'creator' => Auth::id(),
            'assignee' => $validated['assignee'] ?? null,
            'status' => TaskStatus::OPEN,
            'summary' => $validated['summary'],
            'comments' => [],
        ]);

        return new TaskResource($task);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Retrieve a task by ID",
     *     tags={"Tasks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the task",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task found",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=404, description="Task not found")
     * )
     */
    public function show(Task $task)
    {
        // $task = Task::with(['creator', 'assignee'])->findOrFail($task->id);
        $task->load(['creator', 'assignee']);
        return new TaskResource($task);
    }

    /**
     * @OA\Patch(
     *     path="/api/tasks/{id}",
     *     summary="Update a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="closed"),
     *             @OA\Property(property="assignee", type="string", example="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        $task->update($request->only(['status', 'assignee']));

        return new TaskResource($task);
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Delete a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=204, description="Task deleted"),
     *     @OA\Response(response=404, description="Task not found")
     * )
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/comments",
     *     summary="Add a comment to a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the task",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"text"},
     *             @OA\Property(property="text", type="string", example="Please check the issue before closing.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment added to task",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function addComment(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate([
            'text' => 'required|string',
        ]);

        $comments = $task->comments ?? [];

        $comments[] = [
            'user_id' => Auth::id(),
            'text' => $request->input('text'),
            'timestamp' => now(),
        ];

        $task->comments = $comments;
        $task->save();

        return new TaskResource($task);
    }
}

