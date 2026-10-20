<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicCatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/catalog/categories', [PublicCatalogController::class, 'categories']);
Route::get('/catalog/products', [PublicCatalogController::class, 'index']);
Route::get('/catalog/products/{product:slug}', [PublicCatalogController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('categories', CategoryController::class)->except('show');
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage']);
    Route::patch('/products/{product}/images/{image}/thumbnail', [ProductController::class, 'setThumbnail']);
    Route::apiResource('products', ProductController::class);
});
