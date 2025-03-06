<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('user')->whereIn('status', ['open', 'in_progress']);

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

        // Exclude expired  tasks: only show tasks with no deadline or a deadline in the future.
        $query->where(function ($q) {
            $q->whereNull('deadline')
            ->orWhere('deadline', '>', now());
        });

        $tasks = $query->latest()->paginate(10);

        $categories = Task::distinct('category')->pluck('category');
        $allSkills = Task::pluck('skills')->flatten()->unique();

        return view('welcome', compact('tasks', 'filter', 'search', 'category', 'skills', 'categories', 'allSkills'));
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'category' => 'required|max:255',
            'skills' => 'required|string',
            'deadline' => 'nullable|date|after:today',
        ]);

        $validatedData['user_id'] = auth()->id();
        $validatedData['skills'] = explode(',', $validatedData['skills']);
        $validatedData['status'] = 'open';

        $task = Task::create($validatedData);

        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'category' => $task->category,
            'skills' => $task->skills,
            'status' => $task->status,
            'deadline' => $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d H:i') : 'No deadline',
        ]);
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validatedData = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'category'    => 'required|max:255',
            'skills'      => 'required',
            'deadline'    => 'nullable|date|after:today',
        ]);

        $validatedData['skills'] = explode(',', $validatedData['skills']);

        $task->update($validatedData);

        return response()->json([
            'id'          => $task->id,
            'title'       => $task->title,
            'description' => $task->description,
            'category'    => $task->category,
            'skills'      => $task->skills,
            'status'      => $task->status,
            'deadline'    => $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '',
        ]);
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
        if ($task->user_id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot take your own task.']);
        }

        if (!$task->taker_id) {
            $task->update([
                'taker_id' => Auth::id(),
                'status' => 'in_progress',
            ]);

            return response()->json([
                'success' => true,
                'taker_name' => Auth::user()->name, // Send taker's name to frontend
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Task is already taken.']);
    }


    public function show(Task $task)
    {
        return response()->json($task);
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $now = Carbon::now();

        // Created Tasks: tasks you created that are active (deadline not passed)
        $createdTasks = Task::with('taker')
            ->where('user_id', $user->id)
            ->where(function($query) use ($now) {
                $query->whereNull('deadline')->orWhere('deadline', '>', $now);
            })
            ->latest()
            ->get();

        // Taken Tasks: tasks you accepted that are active
        $takenTasks = Task::with('user')
            ->where('taker_id', $user->id)
            ->where(function($query) use ($now) {
                $query->whereNull('deadline')->orWhere('deadline', '>', $now);
            })
            ->latest()
            ->get();

        // Archived Tasks: tasks (created or taken by you) that are expired or completed
        $archivedTasks = Task::with(['user', 'taker'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('taker_id', $user->id);
            })
            ->where(function($query) use ($now) {
                $query->whereNotNull('deadline')->where('deadline', '<=', $now)
                    ->orWhere('status', 'completed');
            })
            ->latest()
            ->get();

            if ($request->ajax()) {
                return response()->json([
                    'createdTasks' => $createdTasks,
                    'takenTasks'   => $takenTasks,
                    'archivedTasks'=> $archivedTasks,
                ]);
            }

        return view('dashboard', compact('createdTasks', 'takenTasks', 'archivedTasks'));
    }

}

