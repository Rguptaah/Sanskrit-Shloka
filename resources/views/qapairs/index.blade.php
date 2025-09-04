@extends('layouts.app')

@section('title', 'Q&A Pairs')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>Q&A Pairs</h1>
    @can('create', App\Models\QAPair::class)
    <a href="{{ route('qa-pairs.create') }}" class="btn btn-primary">Add New Q&A Pair</a>
    @endcan
</div>

@if($qaPairs->isEmpty())
    <p>No Q&A pairs found.</p>
@else
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Shloka ID</th>
            <th>Question</th>
            <th>Answer</th>
            <th>Approved</th>
            <th>Created By</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($qaPairs as $pair)
            <tr>
                <td>{{ $pair->shloka_id }}</td>
                <td>{{ Str::limit($pair->question, 50) }}</td>
                <td>{{ Str::limit($pair->answer, 50) }}</td>
                <td>
                    @if($pair->approved)
                        <span class="badge bg-success">Yes</span>
                    @else
                        <span class="badge bg-warning text-dark">No</span>
                    @endif
                </td>
                <td>{{ $pair->creator->name ?? 'Unknown' }}</td>
                <td>
                    <a href="{{ route('qa-pairs.show', $pair->id) }}" class="btn btn-sm btn-info">View</a>
                    @can('update', $pair)
                    <a href="{{ route('qa-pairs.edit', $pair->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $qaPairs->links() }}
@endif

@endsection
