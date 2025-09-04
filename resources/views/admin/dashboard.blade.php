@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h1 class="mb-4">Admin Dashboard</h1>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5 class="card-title">Manage Users</h5>
                <p class="card-text">Add, edit or remove users.</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title">Approve Entries</h5>
                <p class="card-text">Review and approve fixed and variable data entries.</p>
                <a href="{{ route('admin.approvals.pending') }}" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <h5 class="card-title">Export Data</h5>
                <p class="card-text">Export all approved data as JSON.</p>
                <a href="{{ route('admin.export.json') }}" class="btn btn-light btn-sm">Export</a>
            </div>
        </div>
    </div>
</div>
@endsection
