<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\AuthController;



Route::get('/lang/{locale}', function ($locale) {

    if (! in_array($locale, ['es', 'en'])) {
        abort(400);
    }

    session(['locale' => $locale]);

    return redirect()->back();

})->name('lang.switch');

Route::middleware(['guest'])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/about_us', function () {
        return view('about_us');
    });
    Route::get('/log_in', function () {
        return view('log_in');
    });
    Route::get('/sign_up', function () {
        return view('sign_up');
    });

    Route::post("/sign_up",[AuthController::class, "sign_up"]);
    Route::post("/log_in",[AuthController::class, "log_in"]);

});
Route::middleware(['usuario'])->group(function () {
    Route::get('/home', function () {
        return view('home');
    });
    
    Route::get('/deudas', function (){
        return view('deudas');
    });
    
    Route::get('/log_out', function () {
        return abort(404);
    });
    Route::post("/log_out",[AuthController::class, "log_out"]);

});
