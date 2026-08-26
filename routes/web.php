<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\PasswordResetController;

use App\Http\Controllers\UserManagementController;


Route::get('/lang/{locale}', function ($locale) {
    if (! in_array($locale, ['es', 'en'])) {
        abort(400);
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');


Route::middleware(['guest'])->group(function () {
    Route::get('/', function () { return view('welcome'); });
    Route::get('/about_us', function () { return view('about_us'); });
    Route::get('/log_in', function () { return view('log_in'); });
    Route::get('/sign_up', function () { return view('sign_up'); });
    Route::get('terms', function () { return view('terms'); });

    Route::post('/sign_up', [AuthController::class, 'sign_up']);
    Route::post('/log_in', [AuthController::class, 'log_in']);
    Route::get('/recover_account', function () { return view('recover_account'); });
    
    // Mostrar formulario para ingresar el correo
    Route::get('/forgot_password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    
    // Recibir el correo y enviar el enlace
    Route::post('/forgot_password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    
    // Vista enviada al correo del usuario (Nombre 'password.reset' requerido por Laravel)
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    
    // Guardar la nueva contraseña
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
    });


Route::middleware(['usuario'])->group(function () {

    Route::get('terms', function () { return view('terms'); });

    // ---------------- Perfil ----------------
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/records', [ProfileController::class, 'resetRecords'])->name('profile.records.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---------------- Configuración ----------------
    Route::get('/config', [ConfiguracionController::class, 'index'])->name('config.index');
    Route::patch('/config/agente', [ConfiguracionController::class, 'actualizarAgente'])->name('config.agente');
    Route::patch('/config/colores', [ConfiguracionController::class, 'actualizarColores'])->name('config.colores');
    Route::delete('/config/colores', [ConfiguracionController::class, 'restaurarColores'])->name('config.colores.restaurar');

    // ---------------- Gestión de usuarios (Moderador y Admin) ----------------
    // "editar" y "eliminar" un usuario se reutilizan de /profile?id=,
    // que ya trae permisos, edición y el modal de eliminación con
    // confirmación de contraseña. Aquí solo van listado y creación.
    Route::middleware(['moderador'])->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');

        Route::middleware(['admin'])->group(function () {
            Route::get('/crear', [UserManagementController::class, 'create'])->name('create');
            Route::post('/crear', [UserManagementController::class, 'store'])->name('store');
        });
    });

    // ---------------- Agente IA ----------------
    Route::get('/agent', [AgentController::class, 'index'])->name('agent.index');
    Route::get('/agent/{conversation}/messages', [AgentController::class, 'messages'])->name('agent.messages');
    Route::post('/agent/chat', [AgentController::class, 'chat'])->name('agent.chat');
    Route::delete('/agent/{conversation}', [AgentController::class, 'destroy']);

    // ---------------- Home / Registros ----------------
    Route::match(['get', 'post'], '/home', [RecordController::class, 'index'])->name('records.index');
    Route::get('/deudas', function () { return view('deudas'); });
    Route::get('/info', function () { return view('deudas'); });

    Route::get('/records/export/excel', [RecordController::class, 'exportExcel'])->name('records.export.excel');
    Route::get('/records/export/pdf', [RecordController::class, 'exportPdf'])->name('records.export.pdf');

    // ---------------- Billeteras ----------------
    Route::get('/wallets/{id}/info', [InfoController::class, 'getWalletInfo'])->name('wallets.info');
    Route::put('/wallets/{id}', [InfoController::class, 'updateWallet'])->name('wallets.update');
    Route::delete('/wallets/{id}', [InfoController::class, 'deleteWallet'])->name('wallets.delete');

    // ---------------- Ingresos ----------------
    Route::get('/incomes/{id}/info', [InfoController::class, 'getInfo']);
    Route::put('/incomes/{id}', [InfoController::class, 'update']);
    Route::delete('/incomes/{id}', [InfoController::class, 'destroy']);

    // ---------------- Transacciones ----------------
    Route::get('/info/transaction/{id}', [InfoController::class, 'getTransactionInfo']);
    Route::put('/info/transaction/{id}', [InfoController::class, 'updateTransaction']);
    Route::delete('/info/transaction/{id}', [InfoController::class, 'deleteTransaction']);

    // ---------------- Metas ----------------
    Route::get('/goals/{id}/info', [InfoController::class, 'getGoalInfo']);
    Route::put('/goals/{id}', [InfoController::class, 'updateGoal']);
    Route::delete('/goals/{id}', [InfoController::class, 'deleteGoal']);

    Route::get('/payment-goals/{id}/info', [InfoController::class, 'getPaymentGoalInfo']);
    Route::put('/payment-goals/{id}', [InfoController::class, 'updatePaymentGoal']);
    Route::delete('/payment-goals/{id}', [InfoController::class, 'deletePaymentGoal']);

    // ---------------- Deudas ----------------
    Route::get('/debts/{id}/info', [InfoController::class, 'getDebtInfo']);
    Route::put('/debts/{id}', [InfoController::class, 'updateDebt']);
    Route::delete('/debts/{id}', [InfoController::class, 'deleteDebt']);

    Route::get('/payment-debts/{id}/info', [InfoController::class, 'getPaymentDebtInfo']);
    Route::put('/payment-debts/{id}', [InfoController::class, 'updatePaymentDebt']);
    Route::delete('/payment-debts/{id}', [InfoController::class, 'deletePaymentDebt']);

    // ---------------- Inversiones ----------------
    Route::get('/investments/{id}/info', [InfoController::class, 'getInvestmentInfo']);
    Route::put('/investments/{id}', [InfoController::class, 'updateInvestment']);
    Route::delete('/investments/{id}', [InfoController::class, 'deleteInvestment']);

    // ---------------- Creación de registros ----------------
    Route::post('/wallet/create', [RecordController::class, 'create_wallet']);
    Route::post('/income/create', [RecordController::class, 'create_income']);
    Route::post('/transaction/create', [RecordController::class, 'create_transaction']);
    Route::post('/investment/create', [RecordController::class, 'investment_create'])->name('investment.create');
    Route::post('/goal/create', [RecordController::class, 'create_goal']);
    Route::post('/payment-goal/create', [RecordController::class, 'create_payment_goal']);
    Route::post('/debt/create', [RecordController::class, 'create_debt']);
    Route::post('/payment-debt/create', [RecordController::class, 'create_paymentdebt']);

    // ---------------- Sesión ---------------- //
    Route::get('/log_out', function () { return abort(404); });
    Route::post('/log_out', [AuthController::class, 'log_out']);
});