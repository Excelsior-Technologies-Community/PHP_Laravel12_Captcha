# PHP_Laravel12_Captcha_Auth

## Project Description

PHP_Laravel12_Captcha_Auth is a Laravel 12 web application that provides a secure user authentication system with a built-in **Math Captcha** to prevent bots and automated attacks.

This project includes full **Register**, **Login**, and **Logout** functionality. Every form is protected with a dynamically generated math question (e.g., `7 + 5 = ?`). Users log in using their **Mobile Number** and **Password**, and are redirected to a protected Dashboard after successful authentication.

The main goal is to demonstrate a clean, secure, and modern authentication flow using Laravel 12 best practices — without relying on any third-party captcha library.

---

## Features

1. **Math Captcha** – Dynamically generated addition, subtraction, or multiplication question on every form load.
2. **Register** – Create a new account with Name, Email, Mobile, and Password.
3. **Login** – Authenticate using Mobile Number and Password.
4. **Logout** – Securely destroy the session and redirect to login.
5. **Protected Dashboard** – Only accessible to authenticated users.
6. **Guest Middleware** – Register and Login pages are inaccessible after login.
7. **Auth Middleware** – Dashboard is inaccessible without login.
8. **Session-based Captcha** – Captcha answer stored server-side in session, not exposed to client.
9. **Glassmorphism UI** – Modern, responsive dark-themed interface using Bootstrap 5.

---

## Technologies Used

1. **PHP 8.2+** – Latest stable version for modern PHP development.
2. **Laravel 12** – Backend framework, handles routing, controllers, middleware, and security.
3. **MySQL** – Database for storing user records.
4. **Blade** – Laravel's templating engine for the UI.
5. **Bootstrap 5.3** – Responsive CSS framework for styling.
6. **Laravel Session** – Stores captcha answer securely on the server side.
7. **Bcrypt** – Password hashing via Laravel's `Hash::make()`.

---

## Installation Steps

---

## STEP 1: Create Laravel 12 Project

### Open terminal / CMD and run:

```bash
composer create-project laravel/laravel PHP_Laravel12_Captcha_Auth "12.*"
```

### Go inside project:

```bash
cd PHP_Laravel12_Captcha_Auth
```

#### Explanation:
Installs a fresh Laravel project named `PHP_Laravel12_Captcha_Auth` with version 12.*.
Moves into the project directory to start working on it.

---

## STEP 2: Get Google reCAPTCHA Keys

> **Note:** This project uses a **custom Math Captcha** (no third-party library needed).
> The Google reCAPTCHA keys below are stored in `.env` for reference / future upgrade only.
> You do **not** need to install any extra package for this project.

### How to get Google reCAPTCHA Keys:

**Step 1:** Go to → [https://www.google.com/recaptcha/admin/create](https://www.google.com/recaptcha/admin/create)

**Step 2:** Login with your Google account.

**Step 3:** Fill in the form:
- **Label** → `PHP_Laravel12_Captcha_Auth` (any name you want)
- **reCAPTCHA type** → Select **reCAPTCHA v2** → "I'm not a robot" Checkbox
- **Domains** → Type `localhost` → Press **Enter**

**Step 4:** Click **Submit**.

**Step 5:** You will get two keys:
- **Site Key** → Used in frontend HTML form
- **Secret Key** → Used in backend server-side verification

### Add keys to `.env`:

```env
CAPTCHA_DRIVER=recaptcha
CAPTCHA_SITE_KEY=your_site_key_here
CAPTCHA_SECRET_KEY=your_secret_key_here
```

#### Explanation:
Google reCAPTCHA keys are stored in `.env` for security — never hardcode them in source files.
Site Key is used in the Blade view. Secret Key is used on the server to verify the response.
This project currently uses Math Captcha, so these keys are for reference / future upgrade only.

---

## STEP 3: Database Setup

### Create database in MySQL / phpMyAdmin:

```
Database name: PHP_Laravel12_Captcha
```

### Update `.env` file:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=PHP_Laravel12_Captcha
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120

CAPTCHA_DRIVER=recaptcha
CAPTCHA_SITE_KEY=your_site_key_here
CAPTCHA_SECRET_KEY=your_secret_key_here
```

> **Important:** Never share your real `APP_KEY`, `CAPTCHA_SITE_KEY`, or `CAPTCHA_SECRET_KEY` publicly on GitHub.
> Always add `.env` to `.gitignore` — Laravel does this by default.

### Run migrations:

```bash
php artisan migrate
```

#### Explanation:
Connects Laravel to MySQL and creates the default `users` and `sessions` tables needed for authentication.

---

## STEP 4: Add `mobile` Column to Users Table

### Create migration:

```bash
php artisan make:migration add_mobile_to_users_table --table=users
```

### Edit the migration file `database/migrations/xxxx_add_mobile_to_users_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mobile');
        });
    }
};
```

### Run migration:

```bash
php artisan migrate
```

#### Explanation:
Adds a unique `mobile` column to the users table because we use mobile number for login instead of email.

---

## STEP 5: Update User Model

### `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```

#### Explanation:
Adds `mobile` to the `$fillable` array so it can be mass-assigned during registration.

---

## STEP 6: Create AuthController

### Run:

```bash
php artisan make:controller AuthController
```

### `app/Http/Controllers/AuthController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function generateCaptcha()
    {
        $num1 = rand(1, 20);
        $num2 = rand(1, 20);

        $operators = ['+', '-', '*'];

        $operator = $operators[array_rand($operators)];

        switch ($operator) {

            case '+':
                $answer = $num1 + $num2;
                break;

            case '-':
                $answer = $num1 - $num2;
                break;

            case '*':
                $answer = $num1 * $num2;
                break;
        }

        session([
            'captcha_answer' => $answer,
            'captcha_question' => "$num1 $operator $num2"
        ]);
    }

    public function showRegister()
    {
        $this->generateCaptcha();

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|min:10|unique:users,mobile',
            'password' => 'required|min:6|confirmed',
            'captcha'  => 'required',
        ]);

        if ($request->captcha != session('captcha_answer')) {

            return back()
                ->withErrors(['captcha' => 'Wrong Captcha'])
                ->withInput();
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registration Successful');
    }

    public function showLogin()
    {
        $this->generateCaptcha();

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile'   => 'required',
            'password' => 'required',
            'captcha'  => 'required',
        ]);

        if ($request->captcha != session('captcha_answer')) {

            return back()
                ->withErrors(['captcha' => 'Wrong Captcha'])
                ->withInput();
        }

        if (Auth::attempt([
            'mobile'   => $request->mobile,
            'password' => $request->password
        ])) {

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'mobile' => 'Invalid Credentials'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
```

#### Explanation:
- `generateCaptcha()` – Creates a random math question (`+`, `-`, `*`) and stores the answer in session.
- `showRegister()` / `showLogin()` – Generates a fresh captcha before rendering the form.
- `register()` – Validates all fields, checks captcha answer, creates user with hashed password.
- `login()` – Validates fields, checks captcha, attempts auth with mobile + password.
- `logout()` – Clears session completely and redirects to login.

---

## STEP 7: Add Routes

### `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

Route::get('/', function () {
    return redirect()->route('login');
});
```

#### Explanation:
- `guest` middleware – Prevents logged-in users from visiting Register/Login pages.
- `auth` middleware – Protects Dashboard and Logout from unauthenticated access.
- Root `/` auto-redirects to Login page.

---

## STEP 8: Create Blade Views

### Create folder structure:

```
resources/views/auth/
```

---

### `resources/views/auth/register.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#0f172a,#1e293b,#334155);
            font-family:Arial,sans-serif;
        }

        .auth-card{
            width:100%;
            max-width:520px;
            background:rgba(255,255,255,0.08);
            backdrop-filter:blur(18px);
            border-radius:25px;
            padding:40px;
            border:1px solid rgba(255,255,255,0.1);
            box-shadow:0 10px 40px rgba(0,0,0,0.35);
        }

        .title{
            color:#fff;
            font-size:34px;
            font-weight:700;
            text-align:center;
            margin-bottom:8px;
        }

        .subtitle{
            color:#cbd5e1;
            text-align:center;
            margin-bottom:30px;
        }

        .form-control{
            height:55px;
            border-radius:14px;
            border:none;
            background:rgba(255,255,255,0.12);
            color:#fff;
            padding-left:18px;
        }

        .form-control::placeholder{
            color:#cbd5e1;
        }

        .form-control:focus{
            background:rgba(255,255,255,0.18);
            color:#fff;
            box-shadow:none;
            border:1px solid #38bdf8;
        }

        .captcha-box{
            background:#fff;
            border-radius:16px;
            padding:14px;
            text-align:center;
            margin-bottom:20px;
        }

        .captcha-text{
            font-size:34px;
            font-weight:800;
            color:#0f172a;
            letter-spacing:3px;
        }

        .btn-auth{
            height:55px;
            border:none;
            border-radius:14px;
            background:linear-gradient(135deg,#06b6d4,#3b82f6);
            color:#fff;
            font-size:18px;
            font-weight:600;
            transition:0.3s;
        }

        .btn-auth:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(59,130,246,0.4);
        }

        .bottom-link{
            text-align:center;
            margin-top:22px;
        }

        .bottom-link a{
            color:#38bdf8;
            text-decoration:none;
            font-weight:600;
        }

        .alert{
            border-radius:14px;
        }

    </style>

</head>
<body>

<div class="auth-card">

    <h1 class="title">Create Account</h1>

    <p class="subtitle">
        Register your secure account
    </p>

    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('register.store') }}" method="POST">

        @csrf

        <div class="mb-3">

            <input type="text"
                   name="name"
                   class="form-control"
                   placeholder="Full Name"
                   required>

        </div>

        <div class="mb-3">

            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="Email Address"
                   required>

        </div>

        <div class="mb-3">

            <input type="text"
                   name="mobile"
                   autocomplete="username"
                   class="form-control"
                   placeholder="Mobile Number"
                   required>

        </div>

        <div class="mb-3">

            <input type="password"
                   name="password"
                   autocomplete="new-password"
                   class="form-control"
                   placeholder="Password"
                   required>

        </div>

        <div class="mb-4">

            <input type="password"
                   name="password_confirmation"
                   autocomplete="new-password"
                   class="form-control"
                   placeholder="Confirm Password"
                   required>

        </div>

        <div class="captcha-box">

            <div class="captcha-text">
                {{ session('captcha_question') }} = ?
            </div>

        </div>

        <div class="mb-4">

            <input type="text"
                   name="captcha"
                   class="form-control"
                   placeholder="Solve Captcha"
                   required>

        </div>

        <button class="btn btn-auth w-100">

            Create Account

        </button>

    </form>

    <div class="bottom-link">

        <a href="{{ route('login') }}">
            Already have an account? Login
        </a>

    </div>

</div>

</body>
</html>
```

---

### `resources/views/auth/login.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#020617,#0f172a,#1e293b);
            font-family:Arial,sans-serif;
        }

        .auth-card{
            width:100%;
            max-width:470px;
            background:rgba(255,255,255,0.08);
            backdrop-filter:blur(18px);
            border-radius:25px;
            padding:40px;
            border:1px solid rgba(255,255,255,0.1);
            box-shadow:0 10px 40px rgba(0,0,0,0.35);
        }

        .title{
            color:#fff;
            font-size:34px;
            font-weight:700;
            text-align:center;
            margin-bottom:8px;
        }

        .subtitle{
            color:#cbd5e1;
            text-align:center;
            margin-bottom:30px;
        }

        .form-control{
            height:55px;
            border-radius:14px;
            border:none;
            background:rgba(255,255,255,0.12);
            color:#fff;
            padding-left:18px;
        }

        .form-control::placeholder{
            color:#cbd5e1;
        }

        .form-control:focus{
            background:rgba(255,255,255,0.18);
            color:#fff;
            box-shadow:none;
            border:1px solid #38bdf8;
        }

        .captcha-box{
            background:#fff;
            border-radius:16px;
            padding:14px;
            text-align:center;
            margin-bottom:20px;
        }

        .captcha-text{
            font-size:34px;
            font-weight:800;
            color:#0f172a;
            letter-spacing:3px;
        }

        .btn-auth{
            height:55px;
            border:none;
            border-radius:14px;
            background:linear-gradient(135deg,#10b981,#059669);
            color:#fff;
            font-size:18px;
            font-weight:600;
            transition:0.3s;
        }

        .btn-auth:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(16,185,129,0.4);
        }

        .bottom-link{
            text-align:center;
            margin-top:22px;
        }

        .bottom-link a{
            color:#38bdf8;
            text-decoration:none;
            font-weight:600;
        }

        .alert{
            border-radius:14px;
        }

    </style>

</head>
<body>

<div class="auth-card">

    <h1 class="title">Welcome Back</h1>

    <p class="subtitle">
        Login to continue
    </p>

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('login.store') }}" method="POST">

        @csrf

        <div class="mb-3">

            <input type="text"
                   name="mobile"
                   autocomplete="username"
                   class="form-control"
                   placeholder="Mobile Number"
                   required>

        </div>

        <div class="mb-4">

            <input type="password"
                   name="password"
                   autocomplete="current-password"
                   class="form-control"
                   placeholder="Password"
                   required>

        </div>

        <div class="captcha-box">

            <div class="captcha-text">
                {{ session('captcha_question') }} = ?
            </div>

        </div>

        <div class="mb-4">

            <input type="text"
                   name="captcha"
                   class="form-control"
                   placeholder="Solve Captcha"
                   required>

        </div>

        <button class="btn btn-auth w-100">

            Login

        </button>

    </form>

    <div class="bottom-link">

        <a href="{{ route('register') }}">
            Create new account
        </a>

    </div>

</div>

</body>
</html>
```

---

### `resources/views/dashboard.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:600px">

    <div class="card shadow border-0 p-5 text-center">

        <h2 class="text-success mb-3">
            Welcome Dashboard
        </h2>

        <h5>
            {{ Auth::user()->name }}
        </h5>

        <p>
            {{ Auth::user()->mobile }}
        </p>

        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button class="btn btn-danger">
                Logout
            </button>

        </form>

    </div>

</div>

</body>
</html>
```

#### Explanation:
- `auth/register.blade.php` – Registration form with Name, Email, Mobile, Password, and Math Captcha.
- `auth/login.blade.php` – Login form with Mobile, Password, and Math Captcha. Shows success flash message after registration.
- `dashboard.blade.php` – Shows logged-in user's name and mobile number with a Logout button.

---

## STEP 9: Run the App

### Start development server:

```bash
php artisan serve
```

### Open in browser:

```
http://127.0.0.1:8000
```

#### Explanation:
Starts the Laravel development server locally. Root URL auto-redirects to the Login page.

---

## Expected Output

### Register Page:
- Glassmorphism dark card with Full Name, Email, Mobile, Password, Confirm Password fields.
- Math captcha (e.g., `14 * 3 = ?`) displayed in a white box.

### Login Page:
- Dark card with Mobile Number and Password fields.
- Math captcha question dynamically generated on every page load.
- Green success message shown after successful registration.

### Dashboard Page:
- Shows the logged-in user's name and mobile number.
- Red Logout button to end the session securely.

---

## Project Folder Structure

```
PHP_Laravel12_Captcha_Auth/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── AuthController.php              # Register, Login, Logout + Math Captcha logic
│   └── Models/
│       └── User.php                            # User model with mobile field in $fillable
├── database/
│   └── migrations/
│       ├── xxxx_create_users_table.php         # Default Laravel users table
│       └── xxxx_add_mobile_to_users_table.php  # Custom migration to add mobile column
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php                 # Login form with Math Captcha
│       │   └── register.blade.php              # Register form with Math Captcha
│       └── dashboard.blade.php                 # Protected dashboard (auth only)
├── routes/
│   └── web.php                                 # Guest + Auth middleware route groups
├── .env                                        # DB config + Google reCAPTCHA keys (reference)
├── .env.example                                # Safe template to share on GitHub
├── artisan
├── composer.json
└── composer.lock
```

---

<<<<<<< HEAD
<img width="1903" height="910" alt="Screenshot 2026-05-18 171214" src="https://github.com/user-attachments/assets/b0bc0f8f-371e-40f6-9d2e-1f90daa1221e" />
<img width="1919" height="906" alt="Screenshot 2026-05-18 171200" src="https://github.com/user-attachments/assets/a0afb17d-bc11-40fe-a166-52c90f53e52d" />
<img width="1919" height="905" alt="Screenshot 2026-05-18 171150" src="https://github.com/user-attachments/assets/30db2482-82d0-492a-ad7d-6f75775dcc31" />
=======
<img width="1903" height="910" alt="Screenshot 2026-05-18 171214" src="https://github.com/user-attachments/assets/e37395d5-fd56-4c16-b326-dfe7f03e4039" />
<img width="1919" height="906" alt="Screenshot 2026-05-18 171200" src="https://github.com/user-attachments/assets/bdc0ad26-e9e0-4606-b22d-76b50c8ba916" />
<img width="1919" height="905" alt="Screenshot 2026-05-18 171150" src="https://github.com/user-attachments/assets/0b286268-365a-41f3-87d1-a97630bae404" />
>>>>>>> development
