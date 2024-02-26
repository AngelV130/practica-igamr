<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Vista pantalla pricipal despues del iniciar sesion
     */
    public function dashboardView () {
        return Inertia::render('Dashboard');
    }

    /**
     * Vista de todos los usuarios (Solo de Administrador)
     */
    public function users() {
        return response()->json([
            'users' => User::with('roles')->take(15)->get(),
            'status' => 200
        ]);
    }

    /**
     * Visa de la informacion del usuario
     */
    public function perfil (Request $request) {
        $user = User::with('roles')->find($request->user()->id);
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->roles->name,
                'status' => $user->status == 1 ? 'Activo' : 'Inactivo'
            ],
            'status' => 200
        ]
        );
    }
}
