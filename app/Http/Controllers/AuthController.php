<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AuthController extends Controller
{

    public function log_in(Request $request){
        $data = $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if(Auth::attempt($data)){
            $request->session()->regenerate();
            return redirect('/home');
        }

        return back()->withErrors([
            'email' => 'el correo electronico es incorrecto'
        ])->onlyInput('email');
    }

    public function sign_up(Request $request){

        $request->validate([
            'name'=>'required|min:3|max:25',
            'email'=>'required|email',
            'password'=>'required|min:3|max:25',
            'repeat_password' => 'same:password'
        ]);

        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=>$request->password
        ]);
        Auth::login($user);
        return redirect('/home');
    }

    public function log_out(Request $request){
        Auth::logout();
        return redirect('/');
    }


}
