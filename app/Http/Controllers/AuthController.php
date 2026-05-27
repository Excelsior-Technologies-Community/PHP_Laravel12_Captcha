<?php

namespace App\Http\Controllers;
use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function generateCaptcha()
    {
        $num1 = rand(1, 20);
        $num2 = rand(1, 20);

        $operators = ['+', '-', '*'];
        $operator = $operators[array_rand($operators)];

        $answer = match ($operator) {
            '+' => $num1 + $num2,
            '-' => $num1 - $num2,
            '*' => $num1 * $num2
        };

        session([
            'captcha_answer' => $answer,
            'captcha_question' => "$num1 $operator $num2"
        ]);
    }

    public function refreshCaptcha()
    {
        $this->generateCaptcha();

        return response()->json([
            'captcha' => session('captcha_question')
        ]);
    }

    public function showRegister()
    {
        $this->generateCaptcha();

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|digits_between:10,12|unique:users,mobile',
            'password' => 'required|min:6|confirmed',
            'captcha' => 'required'
        ]);

        if ($data['captcha'] != session('captcha_answer')) {

            $this->generateCaptcha();

            return back()
                ->withErrors([
                    'captcha' => 'Wrong Captcha'
                ])
                ->withInput();
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password'])
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
        $data = $request->validate([
            'mobile' => 'required',
            'password' => 'required',
            'captcha' => 'required'
        ]);

        $key = Str::lower($data['mobile']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {

            return back()->withErrors([
                'mobile' => 'Too many failed attempts. Try again after 2 minutes.'
            ]);
        }

        if ($data['captcha'] != session('captcha_answer')) {

            RateLimiter::hit($key, 120);

            $this->generateCaptcha();

            return back()
                ->withErrors([
                    'captcha' => 'Wrong Captcha'
                ])
                ->withInput();
        }

        if (
            Auth::attempt([
                'mobile' => $data['mobile'],
                'password' => $data['password']
            ])
        ) {

            RateLimiter::clear($key);

            $request->session()->regenerate();

            Auth::user()->update([
                'last_login' => now()
            ]);

            LoginActivity::create([

                'user_id' => Auth::id(),

                'ip_address' => $request->ip(),

                'user_agent' => $request->userAgent(),

                'login_time' => now(),
            ]);

            return redirect()->route('dashboard');
        }

        RateLimiter::hit($key, 120);

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