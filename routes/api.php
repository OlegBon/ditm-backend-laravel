<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckTokenExpired;

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
// Route::post('/user-image', [UserController::class, 'store']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::group([
    'middleware' => [
        'auth:sanctum',
        CheckTokenExpired::class,
    ]
], function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/current-user', [UserController::class, 'currentUser']);
});

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/order-create', [OrderController::class, 'store']);
Route::get('/order/{id}', [OrderController::class, 'show']);

Route::put('/order/{id}', [OrderController::class, 'update']);
Route::delete('/order/{id}', [OrderController::class, 'delete']);
Route::delete('/order/{orderId}/items/{itemId}', [OrderController::class, 'removeItem']);