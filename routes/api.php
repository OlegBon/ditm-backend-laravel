<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

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