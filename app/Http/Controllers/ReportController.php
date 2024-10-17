<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Generate a report based on filters like status, type, assigned_to, etc.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            // Query Builder for Task
            $query = Task::query();

            // Apply Filters
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('type')) {
                $query->where('type', $request->input('type'));
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

            // Fetch filtered tasks
            $tasks = $query->get();

            // Return the report data
            return response()->json([
                'status' => 'success',
                'message' => 'Report generated successfully',
                'data' => $tasks,
            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate report',
            ], 500);
        }
    }
}
