<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Listado de usuarios (estilo "home": tarjetas + buscador + filtro).
     * Un Moderador jamás ve cuentas de Admin (mismo criterio que
     * ProfileController::resolveTarget).
     *
     * Si la petición viene por AJAX (buscador/filtros/paginación del
     * panel de admin), devolvemos solo el HTML de resultados en JSON
     * en vez de la vista completa, para no recargar la página.
     */
    public function index(Request $request)
    {
        $viewer = auth()->user();
        $search = trim((string) $request->input('search', ''));
        $roleFilter = $request->input('role', '');

        $query = User::query()->orderBy('name');

        if ((int) $viewer->roles_id === 2) {
            $query->where('roles_id', '!=', 3);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, ['1', '2', '3'], true)) {
            $query->where('roles_id', (int) $roleFilter);
        }

        $users = $query->paginate(9)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'       => view('partials.users-grid', [
                    'users' => $users,
                ])->render(),
                'roleFilter' => $roleFilter,
                'search'     => $search,
            ]);
        }

        return view('IndexAdmin', [
            'users'      => $users,
            'search'     => $search,
            'roleFilter' => $roleFilter,
            'esAdmin'    => (int) $viewer->roles_id === 3,
        ]);
    }

    /**
     * Formulario de creación. Ruta protegida con middleware "admin".
     */
    public function create()
    {
        return view('CreateUser');
    }

    /**
     * Crea un nuevo usuario con el rol que elija el Admin.
     * Ruta protegida con middleware "admin".
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|min:3|max:25',
            'email'            => 'required|email|unique:users,email',
            'password'         => ['required', Password::min(8)->mixedCase()->symbols()],
            'repeat_password'  => 'same:password',
            'roles_id'         => 'required|in:1,2,3',
        ], [
            'name.required'        => __('validation.required_name'),
            'email.required'       => __('validation.required_email'),
            'email.email'          => __('validation.invalid_email'),
            'email.unique'         => __('validation.email_taken'),
            'password.required'   => __('validation.required_password'),
            'password.min'         => __('validation.letters', ['min' => 8]),
            'password.mixed'       => __('validation.mixed'),
            'password.symbols'     => __('validation.symbols'),
            'repeat_password.same' => __('validation.passwords_dont_match'),
            'roles_id.required'   => 'Debes seleccionar un rol.',
            'roles_id.in'          => 'El rol seleccionado no es válido.',
        ]);

        // Igual que en ProfileController::updateInfo: asignamos las
        // propiedades directamente y guardamos con save() en vez de
        // User::create($data), para no depender del mass-assignment de
        // Eloquent (si 'roles_id' no está en $fillable del modelo User,
        // create() lo descartaría en silencio y el usuario quedaría
        // siempre con el rol por defecto sin importar lo que se eligió).
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->roles_id = $data['roles_id'];
        $user->save();

        return redirect()
            ->route('usuarios.index')
            ->with('status', "Usuario \"{$user->name}\" creado con éxito.");
    }
}