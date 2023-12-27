<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Products\ProductController;

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

// TODO: API products

Route::prefix('products')->name('api.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('search/{name_product}', [ProductController::class, 'searchByName']);
    Route::patch('{slug}', [ProductController::class, 'update']);
    Route::post('/', [ProductController::class, 'store']);
    Route::delete('{product:slug}', [ProductController::class, 'destroy']);
});
