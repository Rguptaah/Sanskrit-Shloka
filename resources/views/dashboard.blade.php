@extends('layouts.app')

@section('title', 'Welcome Guest')

@section('content')
<div class="text-center mt-5">
    <h1>Welcome to Sanskrit Shloka Data Entry Application</h1>
    <p class="lead">Please login or register to contribute.</p>
    <a href="{{ route('login') }}" class="btn btn-primary me-2">Login</a>
    <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
</div>
@endsection