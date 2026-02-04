@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-start">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $routine->title }}</h1>
                @if($routine->is_active)
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Active</span>
                @endif
            </div>
            @if($routine->note)
                <p class="text-gray-600">{{ $routine->note }}</p>
            @endif
        </div>
        <div class="flex space-x-2">
            @if(!$routine->is_active)
                <form action="{{ route('routines.activate', $routine) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">Activate</button>
                </form>
            @endif
            <a href="{{ route('routines.edit', $routine) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">Edit</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Days Grid -->
    <div class="space-y-8">
        @foreach($routine->routineDays as $day)
            @include('routines.day_detail', ['day' => $day, 'exercises' => $allExercises])
        @endforeach

        <!-- Add Day Form -->
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Training Day</h3>
            <form action="{{ route('routine-days.store', $routine) }}" method="POST" class="flex items-end gap-4">
                @csrf
                <div class="flex-grow">
                    <label for="day_name" class="block text-sm font-medium text-gray-700 mb-1">Day Name (e.g., Push, Legs)</label>
                    <input type="text" name="day_name" placeholder="Push A" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3" required>
                </div>
                <div class="w-24">
                    <label for="order_index" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                    <input type="number" name="order_index" value="{{ $routine->routineDays->count() + 1 }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3" required>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 h-10">Add Day</button>
            </form>
        </div>
    </div>
</div>
@endsection
