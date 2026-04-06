<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request){
        return "logiado";
    }

    public function sign_up(Request $request){

        $request->validate([
            'username'=>'required|min:3|max:25',
            'email'=>'required|email',
            'password'=>'required|min:3|max:25',
            'repeat_password' => 'same:password'
        ]);

        User::create([
            'username'=> $request->username,
            'email'=> $request->email,
            'password'=>$request->password
        ]);
        return redirect('/');
    }

}
