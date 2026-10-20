<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function destroy(Request $request, User $usuario)
    {
        abort_if($usuario->is($request->user()), 403, 'No puedes eliminar tu propia cuenta de administrador.');
        $request->validate(['confirmar_eliminacion' => ['required', 'accepted']]);
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('status', 'usuario-eliminado');
    }

    public function bajaDirigente(Request $request, User $usuario)
    {
        abort_unless($usuario->esDirigente() && $usuario->socio?->estado === 'activo', 422);
        $datos = $request->validate([
            'motivo_baja' => ['required', 'string', 'max:500'],
            'fecha_baja' => ['required', 'date', 'before_or_equal:today'],
        ]);
        $usuario->socio->update($datos + ['estado' => 'inactivo', 'revisado_por' => $request->user()->id]);

        return back()->with('status', 'dirigente-dado-de-baja');
    }

    public function index(Request $request)
    {
        $usuarios = User::with('socio')
            ->when($request->rol, fn ($q, $rol) => $q->where('role', $rol))
            ->when($request->buscar, fn ($q, $texto) => $q->where(function ($q) use ($texto) {
                $q->where('name', 'like', "%{$texto}%")
                  ->orWhere('email', 'like', "%{$texto}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->email))]);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // forceFill: el rol y la verificación de correo no pasan por asignación masiva
        (new User)->forceFill([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'role' => $datos['role'],
            'password' => Hash::make($datos['password']),
            'email_verified_at' => now(),
        ])->save();

        return redirect()->route('usuarios.index')->with('status', 'usuario-creado');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->email))]);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Evita que el sistema se quede sin administradores
        if ($usuario->is($request->user()) && $datos['role'] !== $usuario->role) {
            return back()->withInput()->withErrors([
                'role' => 'No puedes cambiar tu propio rol. Pídele a otro administrador que lo haga.',
            ]);
        }

        $usuario->forceFill([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'role' => $datos['role'],
        ]);

        if (! empty($datos['password'])) {
            $usuario->password = Hash::make($datos['password']);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('status', 'usuario-actualizado');
    }
}
