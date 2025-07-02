<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\DashboardController;

// Homepage route (public)
Route::get('/', function () {
    return view('Homepage.home');
})->name('home');

// Guest routes (untuk user yang belum login)
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Register Routes
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    
    // Forgot Password Routes (untuk user yang belum login)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    
    // Reset Password Routes
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    // Email verification notice
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    
    // Email verification handler
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard')->with('success', 'Email verified successfully!');
    })->middleware(['signed'])->name('verification.verify');
    
    // Resend verification email
    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// Public Event routes - menggunakan view yang sesuai dengan struktur folder
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/browse', [EventController::class, 'browse'])->name('events.browse');
Route::get('/browse', [EventController::class, 'browse'])->name('browse');
Route::get('/events/search', [EventController::class, 'search'])->name('events.search');

// Category routes - Public
Route::get('/categories', [EventCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [EventCategoryController::class, 'show'])->name('categories.show');

// PERBAIKAN: Ganti closure function dengan controller method
Route::get('/create-event', function () {
    try {
        $categories = \App\Models\EventCategory::where('is_active', true)->orderBy('name')->get();
    } catch (\Exception $e) {
        $categories = collect([]);
    }
    
    // Ubah dari 'Homepage.create' menjadi 'create'
    return view('create', compact('categories'));
})->name('create.page');

Route::get('/welcome', function () {
    return view('Homepage.welcome');
})->name('welcome');

Route::get('/detail', function () {
    return view('detail');
})->name('detail');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard.page');

Route::get('/profile', function () {
    return view('profile');
})->name('profile.page');

// Browse page route - menambahkan route untuk browse.blade.php
Route::get('/browse-events', function () {
    return view('browse');
})->name('browse.page');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Routes - menggunakan controller jika ada
    Route::get('/dashboard/app', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard API Routes
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/recent-events', [DashboardController::class, 'getRecentEvents'])->name('dashboard.recent-events');
    Route::get('/dashboard/notifications', [DashboardController::class, 'getNotifications'])->name('dashboard.notifications');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('dashboard.recent-activities');
    
    // Profile Routes
    Route::get('/profile/app', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    
    // ==============================================
    // PASSWORD MANAGEMENT ROUTES (USER SUDAH LOGIN)
    // ==============================================
    
    // Change Password (Langsung di Profile - Internal)
    Route::get('/account/change-password', function() {
        return view('auth.change-password');
    })->name('password.change');
    
    Route::post('/account/change-password', [AuthController::class, 'changePassword'])
        ->name('password.change.update');
    
    // Reset Password via Email (User sudah login tapi lupa password lama)
    Route::get('/account/reset-password', [AuthController::class, 'showResetRequestForm'])
        ->name('password.reset.request');
    
    Route::post('/account/reset-password', [AuthController::class, 'sendResetLinkForLoggedUser'])
        ->name('password.reset.email');
    
    // Event CRUD routes - Protected (harus sebelum route show dengan parameter)
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}/update', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}/delete', [EventController::class, 'destroy'])->name('events.destroy');
    
    // Event Registration routes
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::post('/events/{event}/join', [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{event}/cancel', [EventController::class, 'cancelRegistration'])->name('events.cancel');
    
    // User's events
    Route::get('/my-events', [EventController::class, 'myEvents'])->name('events.my');
    Route::get('/my-events/created', [EventController::class, 'viewCreatedEvents'])->name('events.created');
    Route::get('/my-events/registered', [EventController::class, 'registeredEvents'])->name('events.registered');
});

// Public Event Show Route (harus setelah semua route spesifik lainnya)
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Routes that require authentication but not verification
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // ==============================================
    // ALTERNATIVE FORGOT PASSWORD FOR LOGGED USERS
    // ==============================================
    
    // Untuk user yang sudah login tapi ingin reset password via email
    Route::get('/forgot-password-logged', function() {
        return view('auth.forgot-password-logged');
    })->name('password.forgot.logged');
    
    Route::post('/forgot-password-logged', [AuthController::class, 'sendResetLinkForLoggedUser'])
        ->name('password.forgot.logged.send');
});

// API routes
Route::prefix('api')->name('api.')->group(function () {
    // Public API
    Route::get('/events', [EventController::class, 'getEvents'])->name('events.index');
    Route::get('/events/search', [EventController::class, 'search'])->name('events.search');
    Route::get('/categories', [EventCategoryController::class, 'getCategories'])->name('categories');
    
    // Protected API
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
        Route::get('/my-events', [EventController::class, 'myEvents'])->name('api.my-events');
        Route::get('/events/stats', [EventController::class, 'getEventStats'])->name('events.stats');
    });
});

// Contact/Message routes
Route::post('/contact', function (Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:1000',
    ]);
    
    // Handle contact form submission
    return redirect()->back()->with('success', 'Your message has been sent successfully!');
})->name('contact.submit');

// Newsletter subscription
Route::post('/newsletter', function (Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email|max:255',
    ]);
    
    // Handle newsletter subscription
    return redirect()->back()->with('success', 'Thank you for subscribing!');
})->name('newsletter.subscribe');

// Subscribe route
Route::post('/subscribe', function (Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email|max:255',
    ]);
    
    // Handle subscription
    return redirect()->back()->with('success', 'Thank you for subscribing!');
})->name('subscribe');

// Fallback route
Route::fallback(function () {
    return view('errors.404');
});