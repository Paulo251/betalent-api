<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "email" => "required|email|unique:users",
            "password" => "required|min:6",
            "role" => "required|in:admin,manager,finance,user",
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => $request->role,
        ]);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            "name" => "sometimes|string",
            "email" => "sometimes|email|unique:users,email," . $user->id,
            "password" => "sometimes|min:6",
            "role" => "sometimes|in:admin,manager,finance,user",
        ]);

        if ($request->has("password")) {
            $request->merge(["password" => Hash::make($request->password)]);
        }

        $user->update($request->all());

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(["message" => "Usuário deletado com sucesso."]);
    }
}
