<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Goal;
use App\Models\Income;
use App\Models\Investment;
use App\Models\PaymentDebt;
use App\Models\PaymentGoal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\AgentConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;


class ProfileController extends Controller
{
    /**
     * Resuelve sobre qué usuario se está operando y valida permisos.
     */
    private function resolveTarget($id = null): User
    {
        $viewer = auth()->user();

        if (is_null($id) || (int) $id === (int) $viewer->id) {
            return $viewer;
        }

        // Solo Moderador (2) o Admin (3) pueden gestionar cuentas ajenas.
        abort_unless(in_array($viewer->roles_id, [2, 3]), 403);

        return User::findOrFail($id);
    }

    private function nombreRol(int $rolesId): string
    {
        return match ($rolesId) {
            2 => 'Moderador',
            3 => 'Admin',
            default => 'Usuario',
        };
    }

    public function show(Request $request, $id = null)
    {
        $target = $this->resolveTarget($id);
        $viewer = auth()->user();

        $resumen = [
            'billeteras'    => Wallet::where('user_id', $target->id)->count(),
            'transacciones' => Transaction::where('user_id', $target->id)->count(),
            'ingresos'      => Income::where('user_id', $target->id)->count(),
            'metas'         => Goal::where('user_id', $target->id)->count(),
            'deudas'        => Debt::where('user_id', $target->id)->count(),
            'inversiones'   => Investment::where('user_id', $target->id)->count(),
        ];

        return view('profile', [
            'target'         => $target,
            'rolTarget'      => $this->nombreRol($target->roles_id),
            'esPropio'       => $target->id === $viewer->id,
            'puedeGestionar' => in_array($viewer->roles_id, [2, 3]),
            'resumen'        => $resumen,
        ]);
    }

    /**
     * Actualiza nombre y correo.
     */
    public function updateInfo(Request $request, $id = null)
    {
        $target = $this->resolveTarget($id);

        $request->validate([
            'name'  => 'required|string|min:3|max:25',
            'email' => 'required|email|unique:users,email,' . $target->id,
        ], [
            'name.required'  => __('validation.required_name'),
            'email.required' => __('validation.required_email'),
            'email.email'    => __('validation.invalid_email'),
            'email.unique'   => __('validation.email_taken'),
        ]);

        $target->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Información actualizada con éxito.',
            'data'    => [
                'name'  => $target->name,
                'email' => $target->email,
            ],
        ]);
    }

    /**
     * Cambia la contraseña de $target. Siempre se confirma con la
     * contraseña de quien está autenticado (ver nota de la clase).
     */
    public function updatePassword(Request $request, $id = null)
    {
        $target = $this->resolveTarget($id);

        $request->validate([
            'confirm_password' => 'required',
            'password'         => ['required', Password::min(8)->mixedCase()->symbols()],
            'repeat_password'  => 'same:password',
        ], [
            'confirm_password.required' => 'Debes confirmar tu contraseña actual.',
            'password.required'         => __('validation.required_password'),
            'password.min'              => __('validation.letters', ['min' => 8]),
            'password.mixed'            => __('validation.mixed'),
            'password.symbols'          => __('validation.symbols'),
            'repeat_password.same'      => __('validation.passwords_dont_match'),
        ]);

        if (!Hash::check($request->confirm_password, auth()->user()->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tu contraseña es incorrecta.',
            ], 422);
        }

        $target->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Contraseña actualizada con éxito.',
        ]);
    }

    /**
     * Elimina TODOS los registros financieros (y el historial del
     * asistente) de $target, pero conserva la cuenta.
     */
    public function resetRecords(Request $request, $id = null)
    {
        $target = $this->resolveTarget($id);

        $request->validate([
            'confirm_password' => 'required',
        ], [
            'confirm_password.required' => 'Debes confirmar tu contraseña para continuar.',
        ]);

        if (!Hash::check($request->confirm_password, auth()->user()->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tu contraseña es incorrecta.',
            ], 422);
        }

        DB::transaction(function () use ($target) {
            $this->wipeFinancialRecords($target->id);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Todos los registros financieros fueron eliminados.',
        ]);
    }

    /**
     * Elimina la cuenta de $target por completo (registros + usuario).
     */
    public function destroy(Request $request, $id = null)
    {
        $target = $this->resolveTarget($id);
        $esPropio = $target->id === auth()->id();

        $request->validate([
            'confirm_password' => 'required',
        ], [
            'confirm_password.required' => 'Debes confirmar tu contraseña para continuar.',
        ]);

        if (!Hash::check($request->confirm_password, auth()->user()->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tu contraseña es incorrecta.',
            ], 422);
        }

        DB::transaction(function () use ($target) {
            $this->wipeFinancialRecords($target->id);
            $target->delete();
        });

        if ($esPropio) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'status'   => 'success',
            'message'  => 'La cuenta fue eliminada con éxito.',
            'redirect' => $esPropio ? '/' : null,
        ]);
    }

    /**
     * Borra todo lo que pertenece al usuario $uid, en un orden que
     * respeta las llaves foráneas (primero lo que depende de
     * billeteras/metas/deudas, al final las billeteras mismas), y
     * limpia del disco los iconos/comprobantes asociados.
     *
     * payment_goals y payment_debts NO tienen user_id propio (cuelgan
     * de goal_id / debt_id con cascade), así que se recolectan sus
     * iconos por relación antes de borrar la meta/deuda dueña.
     */
    private function wipeFinancialRecords(int $uid): void
    {
        $iconos = [];

        $recolectar = function ($query) use (&$iconos) {
            $iconos = array_merge($iconos, $query->whereNotNull('icono')->pluck('icono')->all());
        };

        $recolectar(Wallet::where('user_id', $uid));
        $recolectar(Transaction::where('user_id', $uid));
        $recolectar(Income::where('user_id', $uid));
        $recolectar(Goal::where('user_id', $uid));
        $recolectar(Debt::where('user_id', $uid));
        $recolectar(Investment::where('user_id', $uid));
        $recolectar(PaymentGoal::whereHas('goal', fn ($q) => $q->where('user_id', $uid)));
        $recolectar(PaymentDebt::whereHas('debt', fn ($q) => $q->where('user_id', $uid)));

        // Orden: hijos antes que padres. payment_goals/payment_debts se
        // borran solos en cascada al borrar Goal/Debt (igual que en
        // deleteGoal/deleteDebt de InfoController).
        Transaction::where('user_id', $uid)->delete();
        Goal::where('user_id', $uid)->delete();
        Debt::where('user_id', $uid)->delete();
        Investment::where('user_id', $uid)->delete();
        Income::where('user_id', $uid)->delete();
        Wallet::where('user_id', $uid)->delete();

        // Los mensajes se borran solos: agent_messages tiene
        // onDelete('cascade') sobre agent_conversation_id.
        AgentConversation::where('user_id', $uid)->delete();

        foreach (array_unique($iconos) as $icono) {
            Storage::disk('public')->delete($icono);
        }
    }
}
