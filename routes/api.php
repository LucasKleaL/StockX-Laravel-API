<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function () {
    Route::get('/', function() {
        return response()->json([
            'message' => 'StockX Laravel API',
        ]);
    });
    Route::get('/products', [ProductController::class, 'getAll']);
    Route::get('/products/{uid}', [ProductController::class, 'get']);
    Route::post('/products', [ProductController::class, 'add']);
    Route::put('/products/{uid}', [ProductController::class, 'update']);
    Route::delete('/products/{uid}', [ProductController::class, 'delete']);
});


