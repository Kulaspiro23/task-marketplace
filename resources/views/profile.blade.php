@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">User Profile</h1>
                <div class="mb-4">
                    <strong class="text-gray-700">Name:</strong>
                    <p class="mt-1">{{ $user->name }}</p>
                </div>
                <div class="mb-4">
                    <strong class="text-gray-700">Email:</strong>
                    <p class="mt-1">{{ $user->email }}</p>
                </div>
                <div class="mb-4">
                    <strong class="text-gray-700">Joined:</strong>
                    <p class="mt-1">{{ $user->created_at->format('F d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

