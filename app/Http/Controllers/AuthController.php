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
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|min:10|unique:users,mobile',
            'password' => 'required|min:6|confirmed',
            'captcha' => 'required',
        ]);

        if ($request->captcha != session('captcha_answer')) {

            return back()
                ->withErrors([
                    'captcha' => 'Wrong Captcha'
                ])
                ->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
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
            'mobile' => 'required',
            'password' => 'required',
            'captcha' => 'required',
        ]);

        if ($request->captcha != session('captcha_answer')) {

            return back()
                ->withErrors([
                    'captcha' => 'Wrong Captcha'
                ])
                ->withInput();
        }

        if (Auth::attempt([
            'mobile' => $request->mobile,
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