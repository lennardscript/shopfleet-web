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

Route::get('products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('product/search/{name_product}', [ProductController::class, 'searchByName'])->name('api.products.searchByName');
Route::patch('product/{slug}', [ProductController::class, 'update'])->name('api.products.update');
Route::post('product', [ProductController::class, 'store'])->name('api.products.store');
Route::delete('product/{product:slug}', [ProductController::class, 'destroy'])->name('api.products.destroy');
