@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Routine</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('routines.update', $routine) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" id="title" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('title', $routine->title) }}" required>
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note (Optional)</label>
                <textarea name="note" id="note" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('note', $routine->note) }}</textarea>
                @error('note')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between items-center">
                <button type="button" onclick="if(confirm('Are you sure you want to delete this routine?')) document.getElementById('delete-routine-form').submit();" class="text-red-600 hover:text-red-800 text-sm">Delete Routine</button>

                <div class="flex space-x-3">
                    <a href="{{ route('routines.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Routine</button>
                </div>
            </div>
        </form>

        <form id="delete-routine-form" action="{{ route('routines.destroy', $routine) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
