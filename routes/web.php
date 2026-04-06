<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/log_in', function () {
    return view('log_in');
});
Route::get('/sign_up', function () {
    return view('sign_up');
});


Route::post("/sign_up",[AuthController::class, "sign_up"]);
Route::post("/login",[AuthController::class, "login"]);