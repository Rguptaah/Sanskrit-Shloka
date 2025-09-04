@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3>Create Account</h3>
                    <p class="text-muted">Join Sanskrit Shloka data entry system</p>
                </div>

                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" required autofocus>

                            @error('name')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span
                                    class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required>

                            @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Select Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">-- Select Your Role --</option>
                            <option value="fixed_entry" {{ old('role')=='fixed_entry' ? 'selected' : '' }}>
                                Fixed Data Entry (Add Shloka Information)
                            </option>
                            {{-- <option value="variable_entry" {{ old('role')=='variable_entry' ? 'selected' : '' }}>
                                Variable Data Entry (Add Q&A and Context)
                            </option>
                            <option value="approver" {{ old('role')=='approver' ? 'selected' : '' }}>
                                Approver (Review and Approve Entries)
                            </option> --}}
                        </select>
                        <div class="form-text">
                            <small>
                                <strong>Fixed Entry:</strong> Add Sanskrit shlokas and their basic information<br>
                                <strong>Variable Entry:</strong> Add questions, answers, and context to existing
                                shlokas<br>
                                <strong>Approver:</strong> Review and approve submitted entries
                            </small>
                        </div>

                        @error('role')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimum 8 characters</div>

                            @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password_confirmation" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye" id="eyeConfirmIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary" id="registerBtn">
                            <span id="registerSpinner" class="spinner-border spinner-border-sm d-none"
                                role="status"></span>
                            Create Account
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0">
                            Already have an account?
                            <a href="{{ route('login') }}" class="text-decoration-none fw-bold">
                                Login here
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

    // Toggle confirm password visibility
    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const password = document.getElementById('password_confirmation');
        const eyeIcon = document.getElementById('eyeConfirmIcon');
        
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

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('strengthBar');
        
        if (password.length >= 8 && /(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])/.test(password)) {
            // Strong password
            strengthBar.className = 'progress-bar bg-success';
            strengthBar.style.width = '100%';
            strengthBar.textContent = 'Strong';
        } else if (password.length >= 6) {
            // Medium password
            strengthBar.className = 'progress-bar bg-warning';
            strengthBar.style.width = '60%';
            strengthBar.textContent = 'Medium';
        } else if (password.length > 0) {
            // Weak password
            strengthBar.className = 'progress-bar bg-danger';
            strengthBar.style.width = '30%';
            strengthBar.textContent = 'Weak';
        } else {
            strengthBar.style.width = '0%';
            strengthBar.textContent = '';
        }
    });

    // Register form submission with loading state
    document.getElementById('registerForm').addEventListener('submit', function() {
        const registerBtn = document.getElementById('registerBtn');
        const registerSpinner = document.getElementById('registerSpinner');
        
        registerBtn.disabled = true;
        registerSpinner.classList.remove('d-none');
        registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Creating Account...';
    });

    // Password confirmation validation
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
@endpush
@endsection