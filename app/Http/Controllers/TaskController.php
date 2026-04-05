<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller implements HasMiddleware
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); 
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $perPage = min($request->get('per_page', 10), 50);

        $tasks = $request->user()->tasks()
            ->when($request->status === 'completed',
                function ($query) {
                    $query->where('status', 'completed');
                })
            ->when($request->status === 'pending',
                function ($query) {
                    $query->where('status', 'pending');
                })
            ->paginate($perPage);

        return $tasks;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string|min:10|max:50',
            'description' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date'
        ]);

        $task = $request->user()->tasks()->create($fields);

        return $task;
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        
        return $task;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        Gate::authorize('modify', $task);
        
        $fields = $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date'
        ]);

        $task->update($fields);

        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Gate::authorize('modify', $task);
        
        $task->delete();

        return ['message' => 'The task was deleted.'];
    }
}
