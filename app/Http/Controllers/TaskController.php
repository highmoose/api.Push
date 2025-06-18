<?php
// Update your app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Models\TaskModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Get all tasks for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TaskModel::where('user_id', Auth::id());

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by overdue if requested
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Sort by due date, then by priority
        $tasks = $query->orderBy('due_date', 'asc')
                      ->orderByRaw("FIELD(priority, 'high', 'medium', 'low', 'none')")
                      ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Create a new task.
     */
    public function store(Request $request): JsonResponse
    {        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'duration' => 'nullable|integer|min:15|max:480',
            'priority' => ['required', Rule::in(['none', 'low', 'medium', 'high'])],
            'category' => ['required', Rule::in(['general', 'client-related', 'equipment', 'administrative'])],
            'status' => ['nullable', Rule::in(['pending', 'completed'])],
            'reminder' => 'nullable|array',
            'reminder.enabled' => 'boolean',
            'reminder.time' => 'nullable|string|in:15min,30min,1hour,1day',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'pending';

        $task = TaskModel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Get a specific task.
     */
    public function show(TaskModel $task): JsonResponse
    {
        // Ensure the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    /**
     * Update a task.
     */
    public function update(Request $request, TaskModel $task): JsonResponse
    {
        // Ensure the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'duration' => 'nullable|integer|min:15|max:480',
            'priority' => ['sometimes', 'required', Rule::in(['none', 'low', 'medium', 'high'])],
            'category' => ['sometimes', 'required', Rule::in(['general', 'client-related', 'equipment', 'administrative'])],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'completed'])],
            'reminder' => 'nullable|array',
            'reminder.enabled' => 'boolean',
            'reminder.time' => 'nullable|string|in:15min,30min,1hour,1day',
        ]);

        // If marking as completed, set completed_at timestamp
        if (isset($validated['status']) && $validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        }

        // If changing from completed to another status, clear completed_at
        if (isset($validated['status']) && $validated['status'] !== 'completed' && $task->status === 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Delete a task.
     */
    public function destroy(TaskModel $task): JsonResponse
    {
        // Ensure the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Mark a task as completed.
     */
    public function markCompleted(TaskModel $task): JsonResponse
    {
        // Ensure the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $task->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Task marked as completed',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Get task statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        $userId = Auth::id();

        $stats = [
            'total' => TaskModel::where('user_id', $userId)->count(),
            'pending' => TaskModel::where('user_id', $userId)->pending()->count(),
            'in_progress' => TaskModel::where('user_id', $userId)->inProgress()->count(),
            'completed' => TaskModel::where('user_id', $userId)->completed()->count(),
            'overdue' => TaskModel::where('user_id', $userId)->overdue()->count(),
            'high_priority' => TaskModel::where('user_id', $userId)->highPriority()->count(),
            'due_today' => TaskModel::where('user_id', $userId)->dueToday()->count(),
            'due_this_week' => TaskModel::where('user_id', $userId)->dueThisWeek()->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}