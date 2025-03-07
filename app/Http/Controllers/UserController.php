<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
}
