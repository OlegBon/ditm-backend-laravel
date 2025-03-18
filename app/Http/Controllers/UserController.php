<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Реєстрація користувача
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Створюємо користувача
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Генеруємо Sanctum-токен
        $tokenResult = $user->createToken('authToken');
        $plainTextToken = $tokenResult->plainTextToken;

        // Встановлюємо термін дії токена (1 день)
        $lastToken = $user->tokens()->latest('id')->first();
        $lastToken->expires_at = now()->addDays(1);
        $lastToken->save();

        // НЕ повертаємо expires_at, щоб фронтенд про нього не знав
        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $plainTextToken,
            // 'expires_at' => $lastToken->expires_at->timestamp, // прибрали expires_at
        ], 201);
    }

    // Логін користувача
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $user = Auth::user();

        // Створюємо Sanctum-токен
        $tokenResult = $user->createToken('authToken');
        $plainTextToken = $tokenResult->plainTextToken;

        // Встановлюємо термін дії токена (1 день)
        $lastToken = $user->tokens()->latest('id')->first();
        $lastToken->expires_at = now()->addDays(1);
        $lastToken->save();

        // НЕ повертаємо expires_at
        return response()->json([
            'message' => 'Logged in successfully',
            'token'   => $plainTextToken,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    // Логаут користувача (видаляємо поточний Bearer-токен)
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    // Дані про поточного користувача (маршрут захищений 'auth:sanctum')
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Not logged in'], 401);
        }

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ], 200);
    }
}