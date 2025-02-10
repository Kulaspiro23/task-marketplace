<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('user')->where('status', 'open');

        // Apply filter
        $filter = $request->input('filter', 'all');
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        // Apply category filter
        $category = $request->input('category');
        if ($category) {
            $query->where('category', $category);
        }

        // Apply skills filter
        $skills = $request->input('skills', []);
        // If $skills is not an array (for example, if it comes as a comma-separated string), convert it.
        if (!is_array($skills)) {
            $skills = $skills ? explode(',', $skills) : [];
        }
        if (!empty($skills)) {
            $query->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhereJsonContains('skills', $skill);
                }
            });
        }

        // Apply search
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->paginate(10);

        $categories = Task::distinct('category')->pluck('category');
        $allSkills = Task::pluck('skills')->flatten()->unique();

        return view('welcome', compact('tasks', 'filter', 'search', 'category', 'skills', 'categories', 'allSkills'));
    }

    public function userTasks()
    {
        $user = auth()->user();
        $tasks = Task::where('user_id', $user->id)->latest()->get();
        return view('dashboard', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'category' => 'required|max:255',
            'skills' => 'required|string',
        ]);

        $validatedData['user_id'] = auth()->id();
        $validatedData['skills'] = explode(',', $validatedData['skills']);
        $validatedData['status'] = 'open';

        $task = Task::create($validatedData);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'category' => 'required|max:255',
            'skills' => 'required',
        ]);

        $validatedData['skills'] = explode(',', $validatedData['skills']);

        $task->update($validatedData);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    public function takeTask(Task $task)
    {
        if ($task->status !== 'open') {
            return response()->json(['message' => 'This task is not available'], 400);
        }

        $task->update([
            'status' => 'in_progress',
            'student_id' => auth()->id(),
        ]);

        return response()->json($task);
    }

    public function show(Task $task)
    {
        return response()->json($task);
    }
}

