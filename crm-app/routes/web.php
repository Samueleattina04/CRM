<?php
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImapController;
use App\Http\Controllers\OutlookController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Customers
    Route::resource('customers', CustomerController::class);

    // Activities
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::post('/customers/{customer}/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    // Tasks
    Route::resource('tasks', TaskController::class)->except(['create','edit','show']);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');

    // Outlook / Microsoft
    Route::get('/outlook/connect', [OutlookController::class, 'connect'])->name('outlook.connect');
    Route::get('/outlook/callback', [OutlookController::class, 'callback'])->name('outlook.callback');
    Route::post('/outlook/disconnect', [OutlookController::class, 'disconnect'])->name('outlook.disconnect');
    Route::post('/outlook/sync', [OutlookController::class, 'sync'])->name('outlook.sync');

    // IMAP Email Integration (senza Azure AD)
    Route::post('/imap/credentials', [ImapController::class, 'saveCredentials'])->name('imap.credentials');
    Route::post('/imap/sync', [ImapController::class, 'sync'])->name('imap.sync');
    Route::post('/imap/disconnect', [ImapController::class, 'disconnect'])->name('imap.disconnect');
    Route::post('/imap/test', [ImapController::class, 'testConnection'])->name('imap.test');

    // Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

    // Users management (admin only)
    Route::middleware('role:admin|manager')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
});

// Webhook for Microsoft Graph
Route::post('/webhooks/outlook', [\App\Http\Controllers\WebhookController::class, 'outlookNotification'])
    ->name('webhooks.outlook');
