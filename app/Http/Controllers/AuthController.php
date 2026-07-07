<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\App;
use App\Models\User;

class AuthController extends Controller
{
    public function log_in(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            return redirect('/home');
        }

        return back()->withErrors([
            'email' => 'El correo electrónico o la contraseña son incorrectos.'
        ])->onlyInput('email');
    }

    public function sign_up(Request $request)
    {
        if ($request->session()->has('locale')) {
            App::setLocale($request->session()->get('locale'));
        }

    $request->validate([
        'name' => 'required|min:3|max:25',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            \Illuminate\Validation\Rules\Password::min(8)
                ->mixedCase()
                ->symbols()
    ],
    'repeat_password' => 'same:password'
    ], [
        'name.required' => __('validation.required_name'),
        'email.required' => __('validation.required_email'),
        'email.email' => __('validation.invalid_email'),
        'email.unique' => __('validation.email_taken'),
        'password.required' => __('validation.required_password'),

        // OJO AQUÍ: Estas son las llaves específicas que Laravel busca para la regla Password
        'password.min' => __('validation.letters', ['min' => 8]),
        'password.mixed' => __('validation.mixed'),
        'password.symbols' => __('validation.symbols'),

        'repeat_password.same' => __('validation.passwords_dont_match'),
    ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);

        return redirect('/home');
    }

    public function log_out(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}