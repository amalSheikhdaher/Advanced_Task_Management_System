<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TaskResource;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\AssignTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Requests\Tasks\UpdateStatusTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get all tasks with advanced filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {

        // Delegate filtering logic to the TaskService
        $tasks = $this->taskService->getFilteredTasks($request);

        // Return the filtered tasks in JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    /**
     * Store a newly created task.
     *
     * @param  \App\Http\Requests\Tasks\StoreTaskRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        
        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        // Ensure task exists
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Delegate task update to the service
        $updatedTask = $this->taskService->updateTask($task, $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'data' => $updatedTask,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task);
        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully',
            'data' => null,
        ], 200);
    }

    /**
     * Update task status and handle dependencies.
     *
     * @param  \App\Http\Requests\Tasks\UpdateStatusTaskRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(UpdateStatusTaskRequest $request, $id): JsonResponse
    {
        $status = $request->input('status');

        // Update task status and handle dependencies
        $this->taskService->updateStatusTask($id, $status);

        // If task is completed, try to reopen any dependent tasks
        if ($status === 'Completed') {
            $this->taskService->reopenDependentTasks($id);
        }

        return response()->json(['message' => 'Task status updated successfully']);
    }

    /**
     * Assign a task to a user.
     *
     * @param  \App\Http\Requests\Tasks\AssignTaskRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignTask(AssignTaskRequest $request, $id): JsonResponse
    {
        $userId = $request->input('assigned_to');
        $task = $this->taskService->assignTask($id, $userId);

        return response()->json([
            'message' => 'Task assigned successfully.',
            'task' => $task,
        ], 200);
    }

    /**
     * Reassign a task to another user.
     *
     * @param  \App\Http\Requests\Tasks\AssignTaskRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reassignTask(AssignTaskRequest $request, $id): JsonResponse
    {
        // Reassign the task to another user
        $userId = $request->input('assigned_to');
        $task = $this->taskService->assignTask($id, $userId);

        // Return the updated task as a resource
        return response()->json([
            'message' => 'Task reassigned successfully.',
            'task' => $task,
        ], 200);
    }

    /**
     * Add a comment to a task.
     *
     * @param  \App\Http\Requests\StoreCommentRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(StoreCommentRequest $request, $id): JsonResponse
    {
        $result = $this->taskService->addCommentToTask($id, $request->input('comment'));

        return response()->json([
            'message' => 'Comment added successfully',
            'task' => $result['task']
        ]);
    }

    /**
     * Add an attachment to a task.
     *
     * @param  StoreAttachmentRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAttachment(StoreAttachmentRequest $request, $taskId): JsonResponse
    {
        // Validate the file from the request
        $file = $request->file('file');

        // Ensure the file exists
        if (!$file) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        // Call the storeAttachment method from TaskService and pass both the file and taskId
        $attachment = $this->taskService->storeAttachment($file, $taskId);

        return response()->json(['data' => $attachment], 201);
    }

    /**
     * Get all trashed (soft-deleted) tasks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trashed (): JsonResponse
    {
        $task = Task::onlyTrashed()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Tasks successfully', 
            'task' => $task
        ], 200);
    }

    /**
     * Restore a soft-deleted task.
     *
     * @param int $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreTask($taskId): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($taskId);
        $task->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Task restored successfully',
            'task' => $task
        ], 200);
    }

    /**
     * Permanently delete a task.
     *
     * @param int $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($taskId): JsonResponse
    {
        $task = Task::withTrashed()->find($taskId);

        if ($task) {
            $task->forceDelete();  // Permanently deletes the task
            return response()->json([
                'status' => 'success',
                'message' => 'Task permanently deleted',
                'task' => null
            ], 200);
        }
        return response()->json([
            'message' => 'Task not found'
        ], 404);
    }
}
