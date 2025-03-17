<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        return User::with('users')->get();
    }
    public function store(Request $request)
    {
        $existingUser = User::where('name', $request->name)
            ->where('email', $request->email)
            ->first();

        if ($existingUser) {
            return response()->json($existingUser, 200);
        }

        return User::create($request->all());
    }

    public function show($id)
    {
        return User::with('users')->findOrFail($id);
    }

    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        // 'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    $token = Str::random(60);

    $user->api_token = $token;
    $user->save();

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
        'token' => $token
    ], 201);
}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 422);
        }

        $user = Auth::user();

        $token = Str::random(60);

        $user->api_token = $token;
        $user->save();

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $token = substr($authHeader, 7);

        $user = User::where('api_token', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $user->api_token = null;
        $user->save();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
