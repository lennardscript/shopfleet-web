<?php

use App\Http\Controllers\Api\Categories\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Users\UserController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// TODO: agruparlos con un prefix las rutas
/*
Route::prefix('api')->group(function () {

});
*/

// TODO: API Users

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::get('verify-email/{uuid}/{hash}', [UserController::class, 'verifyEmail'])
        ->name('verification.verify');
    Route::post('resend-verification-email', [UserController::class, 'resendVerificationEmail']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('reset-password', [UserController::class, 'resetPassword']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [UserController::class, 'logout']);
        Route::get('profile', [UserController::class, 'userProfile']);
        Route::post('auto-verify-email', [UserController::class, 'autoVerificationEmail']);
        Route::put('update-password', [UserController::class, 'updatePassword']);
        Route::patch('update-profile', [UserController::class, 'updateProfile']);
        Route::delete('delete-account', [UserController::class, 'deleteAccount']);
    });
});

// TODO: API products

Route::prefix('products')->name('api.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('search/{name_product}', [ProductController::class, 'search_name_product']);
    Route::patch('{slug}', [ProductController::class, 'update']);
    Route::post('/', [ProductController::class, 'store']);
    Route::delete('{product:slug}', [ProductController::class, 'destroy']);
});

// TODO: API categories

Route::prefix('categories')->name('api.categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('search/{name_category}', [CategoryController::class, 'search_category']);
    Route::patch('{name_category}', [CategoryController::class, 'update']);
    Route::delete('{category:name_category}', [CategoryController::class, 'destroy']);
});
