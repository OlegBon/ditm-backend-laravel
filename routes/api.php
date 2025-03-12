<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return [[
        'id' => 1,
        'name' => 'John Dow',
    ], [
        'id' => 2,
        'name' => 'Robert Art',
    ]];
});

Route::get('/products', [ProductController::class, 'index']);
Route::post('/product-create', [ProductController::class, 'store']);
Route::post('/product-image', [ImageController::class, 'store']);
Route::get('/product/{id}', [ProductController::class, 'show']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/user-create', [UserController::class, 'store']);
Route::post('/user-image', [UserController::class, 'store']);
Route::get('/user/{id}', [UserController::class, 'show']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);