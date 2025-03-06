@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome to TaskMarketplace</h1>
        <p class="text-gray-600 mb-8">
          TaskMarketplace is a platform where you can find tasks to complete or post tasks for others to work on.
        </p>

        <!-- Filters -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
            <select id="filter" name="filter" class="w-full border rounded px-2 py-1">
              <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All</option>
              <option value="open" {{ $filter == 'open' ? 'selected' : '' }}>Open</option>
              <option value="in_progress" {{ $filter == 'in_progress' ? 'selected' : '' }}>In Progress</option>
              <option value="completed" {{ $filter == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
          </div>
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category:</label>
            <select id="category" name="category" class="w-full border rounded px-2 py-1">
              <option value="">All Categories</option>
              @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="skills" class="block text-sm font-medium text-gray-700 mb-1">Skills:</label>
            <select id="skills" name="skills[]" multiple class="w-full border rounded px-2 py-1">
              <option value="">All Skills</option>
              @foreach($allSkills as $skill)
                <option value="{{ $skill }}" {{ in_array($skill, $skills) ? 'selected' : '' }}>{{ $skill }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search:</label>
            <input type="text" id="search" name="search" placeholder="Search tasks..." value="{{ $search }}" class="w-full border rounded px-2 py-1">
          </div>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Available Tasks</h2>
        
        <!-- Tasks Container -->
        <div id="tasks-container">
          @if($tasks->isEmpty())
            <p class="text-gray-500">No tasks available at the moment.</p>
          @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($tasks as $task)
              <div id="task-{{ $task->id }}" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                <h3 class="text-lg font-semibold text-gray-800">{{ $task->title }}</h3>
                <p class="text-gray-600 mt-2">{{ Str::limit($task->description, 100) }}</p>
                <div class="mt-4 flex flex-col">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium status-{{ $task->id }} 
                      {{ $task->status === 'open' ? 'text-green-600' : ($task->status === 'in_progress' ? 'text-blue-600' : 'text-gray-600') }}">
                      {{ ucfirst($task->status) }}
                    </span>
                    <span class="text-sm text-gray-500">{{ $task->created_at->diffForHumans() }}</span>
                  </div>
                  <span class="text-sm text-gray-500">Posted by: {{ $task->user->name }}</span>
                  <span class="text-sm text-gray-500">Category: {{ $task->category }}</span>
                  <span class="text-sm text-gray-500">Skills: {{ implode(', ', $task->skills) }}</span>
                  <span class="text-sm text-gray-500">Deadline: {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->toDayDateTimeString() : 'No deadline' }}</span>
                  <span class="text-sm text-gray-500 taker-{{ $task->id }}">Taker: {{ $task->taker ? $task->taker->name : 'No taker yet' }}</span>

                  @if(Auth::check() && Auth::id() !== $task->user_id && !$task->taker_id)
                    <button class="accept-task-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm" 
                      data-task-id="{{ $task->id }}">
                      Accept Task
                    </button>
                  @endif
                </div>
              </div>
            @endforeach
            </div>
          @endif
        </div>

        <!-- Pagination -->
        <div class="mt-4">
          {{ $tasks->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/welcome.js') }}"></script>
@endpush