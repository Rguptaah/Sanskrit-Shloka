@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="mb-4">Welcome, {{ auth()->user()->name }}</h1>

<div class="row g-4">
    @if(auth()->user()->role === 'fixed_entry')
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title">Add Fixed Shloka</h5>
                <p class="card-text">Enter basic shloka data.</p>
                <a href="{{ route('shloka.create') }}" class="btn btn-primary btn-sm">Add Shloka</a>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'variable_entry')
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Add Q&A / Context</h5>
                <p class="card-text">Contribute questions, answers and context.</p>
                <a href="{{ route('qapair.create') }}" class="btn btn-success btn-sm">Add Entry</a>
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <h5 class="card-title">View Existing Entries</h5>
                <p class="card-text">Browse fixed and variable entries to avoid redundancy.</p>
                <a href="{{ route('shloka.index') }}" class="btn btn-info btn-sm">View Shlokas</a>
            </div>
        </div>
    </div>
</div>
@endsection
