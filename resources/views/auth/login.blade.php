@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3>Login to Sanskrit Shloka</h3>
                    <p class="text-muted">Enter your credentials to access the system</p>
                </div>

                @if(session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autofocus>

                        @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>

                        @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary" id="loginBtn">
                            <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Login
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-2">
                            <a href="{{ route('password.request') }}" class="text-decoration-none">
                                Forgot your password?
                            </a>
                        </p>
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="text-decoration-none fw-bold">
                                Register here
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (password.type === 'password') {
            password.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            password.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    });

    // Login form submission with loading state
    document.getElementById('loginForm').addEventListener('submit', function() {
        const loginBtn = document.getElementById('loginBtn');
        const loginSpinner = document.getElementById('loginSpinner');
        
        loginBtn.disabled = true;
        loginSpinner.classList.remove('d-none');
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Logging in...';
    });
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
@endpush
@endsection 