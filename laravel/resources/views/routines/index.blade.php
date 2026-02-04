@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Routines</h1>
        <a href="{{ route('routines.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create Routine
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($routines as $routine)
            <div class="bg-white rounded-lg shadow p-6 border {{ $routine->is_active ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200' }}">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold text-gray-800">{{ $routine->title }}</h2>
                    @if($routine->is_active)
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Active</span>
                    @endif
                </div>
                
                <p class="text-gray-600 mb-4 text-sm">{{ Str::limit($routine->note, 100) }}</p>
                <p class="text-sm text-gray-500 mb-4">Days: {{ $routine->routineDays->count() }}</p>

                <div class="flex justify-between items-center mt-auto">
                    <div class="space-x-2">
                        <a href="{{ route('routines.show', $routine) }}" class="text-blue-600 hover:text-blue-800">View</a>
                        <a href="{{ route('routines.edit', $routine) }}" class="text-gray-600 hover:text-gray-800">Edit</a>
                    </div>
                    
                    @if(!$routine->is_active)
                        <form action="{{ route('routines.activate', $routine) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800 font-medium text-sm">Activate</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                You haven't created any routines yet.
            </div>
        @endforelse
    </div>
</div>
@endsection
