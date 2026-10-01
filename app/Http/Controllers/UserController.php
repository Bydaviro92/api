<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET: primeros 10 usuarios
    public function get()
    {
        return response()->json(
            User::paginate(10)
        );
    }

    // CREATE: crear usuario
    public function create(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email o contraseña incorrectos'
            ], 401);
        }

        return response()->json([
            'message' => 'Login correcto',
            'user' => $user
        ]);
    }

    // ACTUALIZAR USERNAME
    public function update_username(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'username' => 'required|string|max:255',
        ]);

        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json([
                'message' => 'Email o contraseña incorrectos'
            ], 401);
        }

        $user->name = $request->username;
        $user->save();

        return response()->json([
            'message' => 'Username actualizado correctamente',
            'user' => $user
        ]);
    }

    // ACTUALIZAR EMAIL
    public function update_email(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'new_email' => 'required|email|unique:users,email',
        ]);

        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json([
                'message' => 'Email o contraseña incorrectos'
            ], 401);
        }

        $user->email = $request->new_email;
        $user->save();

        return response()->json([
            'message' => 'Email actualizado correctamente',
            'user' => $user
        ]);
    }

    // ACTUALIZAR CONTRASEÑA
    public function update_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);

        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json([
                'message' => 'Email o contraseña incorrectos'
            ], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    // DELETE
    public function delete(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json([
                'message' => 'Email o contraseña incorrectos'
            ], 401);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ]);
    }

    // Comprobar email y contraseña
    private function authenticate(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return null;
        }

        return $user;
    }
}