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
     *
     * IMPORTANTE: las rutas de perfil (/profile, /profile/password,
     * /profile/records) no tienen un segmento {id} en la URL. Cuando la
     * vista genera route('profile.update', ['id' => $target->id]) para
     * que un Admin/Moderador edite a otra persona, Laravel no encuentra
     * un placeholder "id" y lo agrega como query string
     * (?id=123). Por eso aquí lo leemos con $request->input('id'),
     * que revisa tanto el body como el query string.
     */
    private function resolveTarget(Request $request): User
    {
        $viewer = auth()->user();
        $id = $request->input('id');

        if (is_null($id) || (int) $id === (int) $viewer->id) {
            return $viewer;
        }

        // Solo Moderador (2) o Admin (3) pueden gestionar cuentas ajenas.
        abort_unless(in_array($viewer->roles_id, [2, 3]), 403);

        $target = User::findOrFail($id);

        // Un Moderador nunca gestiona (ni ve) cuentas de Admin.
        if ((int) $viewer->roles_id === 2 && (int) $target->roles_id === 3) {
            abort(403);
        }

        return $target;
    }

    /**
     * Un Moderador puede VER y EDITAR cuentas ajenas (no-Admin), pero
     * jamás eliminarlas ni borrar sus registros: eso es exclusivo de Admin.
     * Cualquiera puede seguir eliminando su PROPIA cuenta/registros.
     */
    private function assertPuedeEliminar(User $viewer, User $target): void
    {
        $esPropio = (int) $target->id === (int) $viewer->id;

        if (!$esPropio && (int) $viewer->roles_id !== 3) {
            abort(403, 'Solo un Administrador puede eliminar cuentas ajenas.');
        }
    }

    /**
     * Valida la contraseña de confirmación de $viewer. Se salta por
     * completo cuando un Admin está eliminando la cuenta/registros de
     * OTRA persona: assertPuedeEliminar() ya garantizó que solo un
     * Admin puede llegar hasta aquí en ese caso, así que no hace falta
     * pedirle de nuevo su propia contraseña.
     *
     * Devuelve null si todo OK (o si no aplica), o una JsonResponse de
     * error si la validación/contraseña falló.
     */
    private function validarPasswordSiAplica(Request $request, User $viewer, bool $esPropio)
    {
        if (!$esPropio) {
            return null;
        }

        $request->validate([
            'confirm_password' => 'required',
        ], [
            'confirm_password.required' => 'Debes confirmar tu contraseña para continuar.',
        ]);

        if (!Hash::check($request->confirm_password, $viewer->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tu contraseña es incorrecta.',
            ], 422);
        }

        return null;
    }

    private function nombreRol(int $rolesId): string
    {
        return match ($rolesId) {
            2 => 'Moderador',
            3 => 'Admin',
            default => 'Usuario',
        };
    }

    /**
     * Convierte un user_agent crudo en algo legible tipo "Chrome · Windows".
     * No usa ninguna librería externa, solo heurística por substrings —
     * suficiente para un panel informativo de admin, no para fingerprinting.
     */
    private function descifrarUserAgent(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Dispositivo desconocido';
        }

        $so = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'SO desconocido',
        };

        // El orden importa: Edge y Opera también incluyen "Chrome" en su UA.
        $navegador = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/'), str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome') => 'Safari',
            default => 'Navegador desconocido',
        };

        return "{$navegador} · {$so}";
    }

    /**
     * Trae las sesiones activas de $target desde la tabla "sessions" y las
     * deja en un formato legible para el panel. Solo lectura: no hay forma
     * de cerrar/revocar sesiones desde aquí, tal como se pidió.
     */
    private function obtenerSesiones(Request $request, User $target)
    {
        return DB::table('sessions')
            ->where('user_id', $target->id)
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get()
            ->map(function ($sesion) use ($request) {
                return [
                    'ip'               => $sesion->ip_address ?: 'Desconocida',
                    'dispositivo'      => $this->descifrarUserAgent($sesion->user_agent),
                    'ultima_actividad' => \Carbon\Carbon::createFromTimestamp($sesion->last_activity),
                    'actual'           => $sesion->id === $request->session()->getId(),
                ];
            });
    }

    public function show(Request $request)
    {
        $target = $this->resolveTarget($request);
        $viewer = auth()->user();
        $esPropio = $target->id === $viewer->id;

        $resumen = [
            'billeteras'    => Wallet::where('user_id', $target->id)->count(),
            'transacciones' => Transaction::where('user_id', $target->id)->count(),
            'ingresos'      => Income::where('user_id', $target->id)->count(),
            'metas'         => Goal::where('user_id', $target->id)->count(),
            'deudas'        => Debt::where('user_id', $target->id)->count(),
            'inversiones'   => Investment::where('user_id', $target->id)->count(),
        ];

        // El admin puede ver las sesiones de cualquier usuario; cualquiera
        // puede ver las suyas propias. Un Moderador NO ve sesiones ajenas.
        $puedeVerSesiones = $esPropio || (int) $viewer->roles_id === 3;

        return view('profile', [
            'target'           => $target,
            'rolTarget'        => $this->nombreRol($target->roles_id),
            'esPropio'         => $esPropio,
            'puedeGestionar'   => in_array($viewer->roles_id, [2, 3]),
            'puedeEliminar'    => $esPropio || (int) $viewer->roles_id === 3,
            'puedeCambiarRol'  => !$esPropio && (int) $viewer->roles_id === 3,
            'resumen'          => $resumen,
            'puedeVerSesiones' => $puedeVerSesiones,
            'sesiones'         => $puedeVerSesiones ? $this->obtenerSesiones($request, $target) : collect(),
        ]);
    }

    /**
     * Actualiza nombre y correo. Si quien edita es Admin y la cuenta es
     * de otra persona, además puede reasignarle el rol (roles_id).
     */
    public function updateInfo(Request $request)
    {
        $viewer = auth()->user();
        $target = $this->resolveTarget($request);
        $esPropio = (int) $target->id === (int) $viewer->id;

        $puedeCambiarRol = !$esPropio && (int) $viewer->roles_id === 3;

        $rules = [
            'name'  => 'required|string|min:3|max:25',
            'email' => 'required|email|unique:users,email,' . $target->id,
        ];

        if ($puedeCambiarRol) {
            $rules['roles_id'] = 'required|in:1,2,3';
        }

        $request->validate($rules, [
            'name.required'     => __('validation.required_name'),
            'email.required'    => __('validation.required_email'),
            'email.email'       => __('validation.invalid_email'),
            'email.unique'      => __('validation.email_taken'),
            'roles_id.required' => 'Debes seleccionar un rol.',
            'roles_id.in'       => 'El rol seleccionado no es válido.',
        ]);

        // IMPORTANTE: asignamos las propiedades directamente y guardamos con
        // save() en vez de update($data)/create($data). update()/create()
        // pasan por el mass-assignment de Eloquent: si 'roles_id' no está
        // en $fillable (o sí está en $guarded) del modelo User, Laravel lo
        // descarta EN SILENCIO -- sin error, sin excepción, el resto de
        // campos se guardan normal y nadie se entera de que el rol nunca
        // cambió. Asignar la propiedad directamente (target->roles_id = ..)
        // se salta esa protección por completo, así que esto funciona sin
        // importar cómo esté configurado el modelo.
        $target->name = $request->name;
        $target->email = $request->email;

        if ($puedeCambiarRol) {
            $target->roles_id = (int) $request->roles_id;
        }

        $target->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Información actualizada con éxito.',
            'data'    => [
                'name'     => $target->name,
                'email'    => $target->email,
                'roles_id' => $target->roles_id,
            ],
        ]);
    }

    /**
     * Cambia la contraseña de $target. Siempre se confirma con la
     * contraseña de quien está autenticado (esto no cambia con este
     * ajuste: solo aplica a "eliminar", no a "cambiar contraseña").
     */
    public function updatePassword(Request $request)
    {
        $target = $this->resolveTarget($request);

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
     *
     * Si un Admin gestiona la cuenta de otra persona, no se le pide
     * confirmar con contraseña (assertPuedeEliminar ya garantiza que
     * solo un Admin puede llegar aquí para una cuenta ajena).
     */
    public function resetRecords(Request $request)
    {
        $viewer = auth()->user();
        $target = $this->resolveTarget($request);
        $this->assertPuedeEliminar($viewer, $target);
        $esPropio = (int) $target->id === (int) $viewer->id;

        if ($error = $this->validarPasswordSiAplica($request, $viewer, $esPropio)) {
            return $error;
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
     *
     * Si un Admin gestiona la cuenta de otra persona, no se le pide
     * confirmar con contraseña; solo si elimina su PROPIA cuenta.
     */
    public function destroy(Request $request)
    {
        $viewer = auth()->user();
        $target = $this->resolveTarget($request);
        $this->assertPuedeEliminar($viewer, $target);
        $esPropio = $target->id === auth()->id();

        if ($error = $this->validarPasswordSiAplica($request, $viewer, $esPropio)) {
            return $error;
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
            // Si es tu propia cuenta, ya cerramos tu sesión arriba -> a inicio.
            // Si un Admin borró la cuenta de OTRA persona, su sesión sigue
            // activa: lo mandamos de vuelta al panel de usuarios, no a "/".
            'message'  => 'La cuenta fue eliminada con éxito.',
            'redirect' => $esPropio ? '/' : route('usuarios.index'),
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