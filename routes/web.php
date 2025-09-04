<?php

use App\Http\Controllers\ShlokaController;
use App\Http\Controllers\QAPairController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ExportController;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;


Route::get('/', function () {
    if(auth()->check()) {
        $role = auth()->user()->role;
        switch($role) {
            case 'admin': return redirect()->route('admin.dashboard');
            case 'approver': return redirect()->route('approver.dashboard');
            case 'fixed_entry':
            case 'variable_entry':
                return redirect()->route('user.dashboard');
            default: return view('dashboard');
        }
    }
    return view('dashboard');
});

// Dashboards
Route::middleware(['auth'])->group(function() {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard')->middleware('role:admin');

    Route::get('/approver/dashboard', function () {
        return view('approver.dashboard');
    })->name('approver.dashboard')->middleware('role:approver');

    Route::get('/dashboard', function() {
        return view('user.dashboard');
    })->name('user.dashboard')->middleware('role:fixed_entry|variable_entry');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/shloka', function () {
    return view('shlokas.index');
});
Route::get('/shloka/create', function () {
    return view('shlokas.create');
});
Route::get('/qapairs', function () {
    return view('qapairs.index');
});
Route::get('/qapairs/create', function () {
    return view('qapairs.create');
});

Route::middleware(['auth'])->group(function () {

    // Fixed data species by fixed_entry role
    Route::middleware(['role:fixed_entry,admin,approver'])->group(function() {
        Route::resource('shlokas', ShlokaController::class);
    });

    // QA pairs by variable entry users
    Route::middleware(['role:variable_entry,admin,approver'])->group(function() {
        Route::resource('qa-pairs', QAPairController::class);
    });

    // Approval routes (admin, approver)
    Route::middleware(['role:admin,approver'])->group(function() {
        Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('approvals/{type}/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/{type}/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // Export
    Route::middleware(['role:admin'])->get('/export/shlokas-json', [ExportController::class, 'exportJson'])->name('export.shlokas-json');

});
