<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
//Import AuthController
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('book')->group(function(){
    Route::get('index',[BookController::class, 'index']);
    Route::get('show/{id}',[BookController::class, 'show']);
    Route::post('store',[BookController::class, 'store']);
    Route::put('update/{id}',[BookController::class, 'update']);
    Route::delete('destroy/{id}',[BookController::class, 'destroy']);
});

//Authentication is not required for these endpoints
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

//Authentication is required for these endpoints (apply middleware auth:sanctum)
Route::group(['middleware' => ["auth:sanctum"]], function () {
    Route::get('userProfile', [AuthController::class, 'userProfile']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::put('changePassword', [AuthController::class, 'changePassword']);
    Route::post('review',[BookController::class, 'addReview']);
    Route::put('updateReview/{id}',[BookController::class, 'updateBookReview']);
});
