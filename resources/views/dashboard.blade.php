@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm sm:rounded-lg">
      <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
        
        <!-- Create Task Button -->
        <button id="createTaskBtn" class="mb-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Create New Task
        </button>

        <!-- Tab Navigation -->
        <div class="mb-4 flex space-x-2">
          <button id="tab-created" class="tab-button active px-4 py-2 border border-blue-500 text-blue-500" data-tab="created">
            Created Tasks
          </button>
          <button id="tab-taken" class="tab-button px-4 py-2 border border-gray-300 text-gray-500" data-tab="taken">
            Taken Tasks
          </button>
          <button id="tab-archived" class="tab-button px-4 py-2 border border-gray-300 text-gray-500" data-tab="archived">
            Archived Tasks
          </button>
        </div>

        <!-- Filters & Sorting -->
        <div class="flex justify-between items-center mb-4">
          <input type="text" id="searchInput" placeholder="Search tasks..." class="border rounded px-2 py-1 w-1/2">
        </div>

        <!-- Tab Contents -->
        <div id="tab-content-created" class="tab-content">
          @forelse($createdTasks as $task)
            <div class="card p-4 border rounded mb-4">
              <h3 class="text-lg font-semibold">{{ $task->title }}</h3>
              <p>{{ $task->description }}</p>
              <p class="text-sm text-gray-500">Category: {{ $task->category }}</p>
              <p class="text-sm text-gray-500">Skills: {{ implode(', ', $task->skills) }}</p>
              <p class="text-sm text-gray-500">Deadline: {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d H:i') : 'No deadline' }}</p>
              <p class="text-sm text-gray-500">Status: {{ ucfirst($task->status) }}</p>
              <p class="text-sm text-gray-500">Taker: {{ $task->taker ? $task->taker->name : 'No taker yet' }}</p>
              <!-- Action Buttons for tasks you created -->
              <div class="flex space-x-2 mt-2">
                <button class="edit-btn bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm"
                  data-task='@json($task)'>
                  Edit
                </button>
                <button class="delete-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm"
                  data-task-id="{{ $task->id }}">
                  Delete
                </button>
              </div>
            </div>
          @empty
            <p class="text-gray-500">No created tasks available.</p>
          @endforelse
        </div>

        <div id="tab-content-taken" class="tab-content hidden">
          @forelse($takenTasks as $task)
            <div class="card p-4 border rounded mb-4">
              <h3 class="text-lg font-semibold">{{ $task->title }}</h3>
              <p>{{ $task->description }}</p>
              <p class="text-sm text-gray-500">Category: {{ $task->category }}</p>
              <p class="text-sm text-gray-500">Skills: {{ implode(', ', $task->skills) }}</p>
              <p class="text-sm text-gray-500">Deadline: {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d H:i') : 'No deadline' }}</p>
              <p class="text-sm text-gray-500">Status: {{ ucfirst($task->status) }}</p>
              <p class="text-sm text-gray-500">Posted by: {{ $task->user->name }}</p>
            </div>
          @empty
            <p class="text-gray-500">No taken tasks available.</p>
          @endforelse
        </div>

        <div id="tab-content-archived" class="tab-content hidden">
          @forelse($archivedTasks as $task)
            <div class="card p-4 border rounded mb-4 bg-gray-100">
              <h3 class="text-lg font-semibold">{{ $task->title }}</h3>
              <p>{{ $task->description }}</p>
              <p class="text-sm text-gray-500">Category: {{ $task->category }}</p>
              <p class="text-sm text-gray-500">Skills: {{ implode(', ', $task->skills) }}</p>
              <p class="text-sm text-gray-500">Deadline: {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d H:i') : 'No deadline' }}</p>
              <p class="text-sm text-gray-500">Status: {{ ucfirst($task->status) }}</p>
              <p class="text-sm text-gray-500">Posted by: {{ $task->user->name }}</p>
              @if($task->taker)
                <p class="text-sm text-gray-500">Taker: {{ $task->taker->name }}</p>
              @endif
            </div>
          @empty
            <p class="text-gray-500">No archived tasks available.</p>
          @endforelse
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Create Task Modal -->
<div id="createTaskModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <form id="createTaskForm">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="mb-4">
            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
            <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea name="description" id="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
          </div>
          <div class="mb-4">
            <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
            <input type="text" name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="skills" class="block text-gray-700 text-sm font-bold mb-2">Skills (comma-separated):</label>
            <input type="text" name="skills" id="skills" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="deadline" class="block text-gray-700 text-sm font-bold mb-2">Deadline:</label>
            <input type="datetime-local" name="deadline" id="deadline" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            Create Task
          </button>
          <button type="button" id="closeCreateModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div id="editTaskModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <form id="editTaskForm">
        <input type="hidden" id="editTaskId" name="taskId">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="mb-4">
            <label for="editTitle" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
            <input type="text" name="title" id="editTitle" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="editDescription" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea name="description" id="editDescription" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
          </div>
          <div class="mb-4">
            <label for="editCategory" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
            <input type="text" name="category" id="editCategory" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="editSkills" class="block text-gray-700 text-sm font-bold mb-2">Skills (comma-separated):</label>
            <input type="text" name="skills" id="editSkills" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="editDeadline" class="block text-gray-700 text-sm font-bold mb-2">Deadline:</label>
            <input type="datetime-local" name="deadline" id="editDeadline" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            Update Task
          </button>
          <button type="button" id="closeEditModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Task Confirmation Modal -->
<div id="deleteTaskModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Delete Task
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to delete this task? This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" id="confirmDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
          Delete
        </button>
        <button type="button" id="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
