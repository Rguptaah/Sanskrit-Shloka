@extends('layouts.app')

@section('title', 'All Shlokas')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>All Shlokas</h1>
    @can('create', App\Models\Shloka::class)
    <a href="{{ route('shlokas.create') }}" class="btn btn-primary">Add New Shloka</a>
    @endcan
</div>

@if($shlokas->isEmpty())
    <p>No shlokas found.</p>
@else
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Sanskrit Shloka</th>
            <th>Transliteration</th>
            <th>Category</th>
            <th>Approved</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shlokas as $shloka)
        <tr>
            <td>{{ $shloka->id }}</td>
            <td>{{ Str::limit($shloka->sanskrit_shloka, 50) }}</td>
            <td>{{ Str::limit($shloka->transliteration, 50) }}</td>
            <td>{{ $shloka->category ?? '-' }}</td>
            <td>
                @if($shloka->approved)
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-warning text-dark">No</span>
                @endif
            </td>
            <td>
                <a href="{{ route('shlokas.show', $shloka->id) }}" class="btn btn-sm btn-info">View</a>
                @can('update', $shloka)
                <a href="{{ route('shlokas.edit', $shloka->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                @endcan
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $shlokas->links() }}

@endif

@endsection
