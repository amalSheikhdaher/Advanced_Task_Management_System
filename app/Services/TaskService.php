<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Attachment;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Symfony\Component\HttpFoundation\File\Exception\FileException;


class TaskService
{
    /**
     * Fetch tasks based on filters with caching and error handling.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function getFilteredTasks(Request $request)
    {
        try {
            // Initialize query builder for Task model
            $query = Task::query();

            // Apply filters if present in the request
            if ($request->has('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('assigned_to')) {
                $query->where('assigned_to', $request->input('assigned_to'));
            }

            if ($request->has('due_date')) {
                $query->whereDate('due_date', $request->input('due_date'));
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->input('priority'));
            }
            
            // Return the filtered tasks collection
            return $query->get();
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to retrieve tasks: ' . $e->getMessage());
        }
    }

    /**
     * Create a new task.
     *
     * @param  array  $data
     * @return \App\Models\Task
     */
    public function createTask($data): Task
    {
        try {
            // Create and return the task
            return Task::create([
                'title'       => $data['title'],
                'description' => $data['description'],
                'type'        => $data['type'],
                'status'      => $data['status'],
                'priority'    => $data['priority'],
                'due_date'    => $data['due_date'],
                // 'assigned_to' => $data['assigned_to']
            ]);
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to created task: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing task with new data.
     *
     * @param Task $task The task model to update.
     * @param array $data An array containing the updated task data.
     * @return bool True if the update was successful, false otherwise.
     * @throws Exception If an error occurs while updating the task.
     */
    public function updateTask(Task $task, $data): Task
    {
        try {
            $task->title = $data['title'] ?? $task->title;
            $task->description = $data['description'] ?? $task->description;
            $task->type = $data['type'] ?? $task->type;
            $task->status = $data['status'] ?? $task->status;
            $task->priority = $data['priority'] ?? $task->priority;
            $task->due_date = $data['due_date'] ?? $task->due_date;
            $task->assigned_to = $data['assigned_to'] ?? $task->assigned_to;
            $task->save();
            return $task;
        } catch (Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage());
            throw new Exception('Task update failed.');
        }
    }

    /**
     * Update the status of a task, automatically blocking it if it has unfinished dependencies.
     *
     * @param  int  $taskId
     * @param  string  $status
     * @return bool
     */
    public function updateStatusTask($taskId, $status)
    {
        $task = Task::findOrFail($taskId);

        // Check if the task has any dependencies that are not completed
        if ($this->checkIfTaskIsBlocked($task)) {
            $status = 'Blocked';
        }

        $task->status = $status;
        return $task->save();
    }

    /**
     * Check if a task is blocked due to unfinished dependencies.
     *
     * @param  \App\Models\Task  $task
     * @return bool
     */
    public function checkIfTaskIsBlocked(Task $task)
    {
        // Check if there are any dependencies that are not completed
        return $task->dependencies()->whereHas('dependsOn', function ($query) {
            $query->where('status', '!=', 'Completed');
        })->exists();
    }

    /**
     * Reopen tasks that were blocked, if all dependencies are now completed.
     *
     * @param  int  $taskId
     * @return void
     */
    public function reopenDependentTasks($taskId)
    {
        // Find all tasks that depend on this task
        $tasksDependingOnThis = Task::whereHas('dependencies', function ($query) use ($taskId) {
            $query->where('depends_on_task_id', $taskId);
        })->get();

        // Loop through each dependent task
        foreach ($tasksDependingOnThis as $task) {
            // Check if the dependent task can be reopened (if all dependencies are now completed)
            if (!$this->checkIfTaskIsBlocked($task)) {
                $task->status = 'Open';
                $task->save();
            }
        }
    }

    /**
     * Assign a task to a user.
     *
     * @param  int  $taskId
     * @param  int  $userId
     * @return \App\Models\Task
     */
    public function assignTask($taskId, $userId): Task
    {
        $task = Task::findOrFail($taskId);
        $user = User::findOrFail($userId);

        $task->assigned_to = $user->id;
        $task->save();

        return $task;
    }

    /**
     * Add a comment to a task.
     *
     * @param  int  $taskId
     * @param  string  $commentText
     * @return array
     */
    public function addCommentToTask($taskId, $commentText): array
    {
        // Find the task
        $task = Task::findOrFail($taskId);

        // Create a new comment related to the task
        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $commentText,
        ]);

        // Reload the task with its newly created comment
        $task->load('comments'); // Load comments relationship

        return [
            'task' => $task,
            'comment' => $comment,
        ];
    }

    /**
     * Store a PDF or Word file.
     *
     * @param  mixed  $file
     * @return \App\Models\Attachment
     * @throws \Exception
     */
    public function storeAttachment($file, $taskId)
    {
        try {
            $originalName = $file->getClientOriginalName();

            // Ensure the file extension is valid and there is no path traversal in the file name
            if (preg_match('/\.[^.]+\./', $originalName)) {
                throw new Exception(trans('general.notAllowedAction'), 403);
            }

            // Check for path traversal attack
            if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
                throw new Exception(trans('general.pathTraversalDetected'), 403);
            }

            // Validate the MIME type to ensure it's either a PDF or Word document
            $allowedMimeTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $mime_type = $file->getClientMimeType();

            if (!in_array($mime_type, $allowedMimeTypes)) {
                throw new FileException(trans('general.invalidFileType'), 403);
            }

            // Generate a safe, random file name
            $fileName = Str::random(32);
            $extension = $file->getClientOriginalExtension(); // Safe way to get file extension

            // File storage path for attachments
            $filePath = "Attachments/{$fileName}.{$extension}";

            // Store the file securely
            $path = Storage::disk('local')->putFileAs('Attachments', $file, $fileName . '.' . $extension);

            // Get the full URL path of the stored file
            $url = Storage::disk('local')->url($path);

            // Store file metadata in the database
            $attachment = Attachment::create([
                'file_name' => $originalName,
                'file_path' => $url,
                'mime_type' => $mime_type,
                'attachable_type' => Task::class,
                'attachable_id' => $taskId,
            ]);

            return $attachment;
        } catch (Exception $e) {
            // Rethrow the exception to be caught by the controller if necessary
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete a specific task from the database.
     *
     * @param Task $task The task model to delete.
     * @return void
     * @throws Exception If an error occurs while deleting the task.
     */
    public function deleteTask(Task $task)
    {
        try {
            $task->delete();
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to deleted task: ' . $e->getMessage());
        }
    }
}
