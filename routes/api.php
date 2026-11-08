<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GalleryItemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicCatalogController;
use App\Http\Controllers\Api\PublicSiteContentController;
use App\Http\Controllers\Api\SiteContactController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/catalog/categories', [PublicCatalogController::class, 'categories']);
Route::get('/catalog/products', [PublicCatalogController::class, 'index']);
Route::get('/catalog/products/{product:slug}', [PublicCatalogController::class, 'show']);
Route::get('/site/contact', [PublicSiteContentController::class, 'contact']);
Route::get('/site/gallery', [PublicSiteContentController::class, 'gallery']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class)->except('show');
    Route::apiResource('categories', CategoryController::class)->except('show');
    Route::get('site-contact', [SiteContactController::class, 'show']);
    Route::put('site-contact', [SiteContactController::class, 'update']);
    Route::apiResource('gallery-items', GalleryItemController::class);
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage']);
    Route::patch('/products/{product}/images/{image}/thumbnail', [ProductController::class, 'setThumbnail']);
    Route::apiResource('products', ProductController::class);
});
