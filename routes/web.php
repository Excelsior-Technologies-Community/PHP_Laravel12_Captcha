<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/refresh-captcha', [AuthController::class, 'refreshCaptcha']);
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {

        $search = request('search');

        $activities = \App\Models\LoginActivity::where('user_id', auth()->id())

            ->when($search, function ($query) use ($search) {

                $query->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%");
            })

            ->oldest()

            ->paginate(4);

        return view('dashboard', compact('activities'));

    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', function () {
    return redirect()->route('login');
});