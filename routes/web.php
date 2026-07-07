<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecordController;

Route::get('/lang/{locale}', function ($locale) {
    if (! in_array($locale, ['es', 'en'])) {
        abort(400);
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

// --- RUTAS PARA INVITADOS ---
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () { return view('welcome'); });
    Route::get('/about_us', function () { return view('about_us'); });
    Route::get('/log_in', function () { return view('log_in'); });
    Route::get('/sign_up', function () { return view('sign_up'); });
    Route::get('terms', function () { return view('terms'); });
    
    Route::post("/sign_up", [AuthController::class, "sign_up"]);
    Route::post("/log_in", [AuthController::class, "log_in"]);
});

// --- RUTAS PARA USUARIOS AUTENTICADOS ---
// --- RUTAS PARA USUARIOS AUTENTICADOS ---
Route::middleware(['usuario'])->group(function () {
    
    Route::match(['get', 'post'], '/home', [RecordController::class, 'index'])->name('records.index');
    
    Route::get('/deudas', function () { return view('deudas'); });

    Route::get('/info', function () { return view('deudas'); });
    
    // Procesamiento de formularios de creación...
    Route::post('/wallet/create', [RecordController::class, 'create_wallet']);
    Route::post('/income/create', [RecordController::class, 'create_income']);
    Route::post('/transaction/create', [RecordController::class, 'create_transaction']);
    Route::post('/investment/create', [RecordController::class, 'investment_create'])->name('investment.create');
    Route::post('/goal/create', [RecordController::class, 'create_goal']);
    Route::post('/payment-goal/create', [RecordController::class, 'create_payment_goal']);
    Route::post('/debt/create', [RecordController::class, 'create_debt']);
    Route::post('/payment-debt/create', [RecordController::class, 'create_paymentdebt']);
    
    Route::get('/log_out', function () { return abort(404); });
    Route::post("/log_out", [AuthController::class, "log_out"]);
});