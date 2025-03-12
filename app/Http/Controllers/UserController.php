<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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
        return User::with('user-images')->findOrFail($id);
    }

    /**
     * Регистрация нового пользователя.
     * Ожидает поля: name, email, password, password_confirmation
     * Возвращает JSON с данными пользователя.
     */
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Создаём пользователя
    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    // Генерируем token
    $token = Str::random(60);

    // Сохраняем token в поле api_token
    $user->api_token = $token;
    $user->save();

    // Возвращаем JSON с user и token
    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
        'token' => $token // <-- теперь фронтенд получит token
    ], 201);
}

    /**
     * Логин пользователя.
     * Ожидает поля: email, password
     * Возвращает JSON с токеном (api_token).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Проверяем email/password через Auth::attempt()
        // (Хотя можно и вручную искать user, сверять Hash::check, но Auth::attempt удобно)
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 422);
        }

        // Успешный логин, получаем пользователя
        $user = Auth::user();

        // Генерируем случайный токен
        $token = Str::random(60);

        // Сохраняем в поле api_token
        $user->api_token = $token;
        $user->save();

        // Возвращаем клиенту
        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,  // <-- ВАЖНО: клиенту нужно сохранить этот токен
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Логаут пользователя.
     * "Сбрасываем" токен, чтобы он стал недействительным.
     */
    public function logout(Request $request)
    {
        // Ищем заголовок Authorization: Bearer <token>
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $token = substr($authHeader, 7);

        // Ищем пользователя по этому токену
        $user = User::where('api_token', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Сбрасываем токен
        $user->api_token = null;
        $user->save();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
