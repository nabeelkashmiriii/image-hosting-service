<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usercontroller;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/signup', [UserController::class,'signup']);
Route::post('/login', [UserController::class,'login'])->middleware('validated');
Route::get('/logout', [UserController::class,'logout']);
Route::get('/verifyEmail/{email}',[UserController::class,'verify']);
Route::post('password/email', [UserController::class, 'forgot']);
Route::put('update/profile/{id}', [UserController::class, 'updateProfile'])->middleware('EnsureToken');
Route::post('/forgot', [UserController::class,'forgotPassword']);
Route::get('/resetpass/{password}/{email}',[UserController::class,'resetPassword']);
