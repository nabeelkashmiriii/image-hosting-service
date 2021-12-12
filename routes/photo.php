<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotosController;

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

Route::middleware('EnsureToken')->group(function () {
    Route::post('upload/photo', [PhotosController::class, 'uploadPhoto']);
    Route::delete('delete/photo', [PhotosController::class, 'deletePhoto']);
    Route::put('setprivacy/photo', [PhotosController::class, 'setPrivacy']);
    Route::get('list/photos', [PhotosController::class, 'listAllPhotos']);
    Route::get('search/photos', [PhotosController::class, 'searchPhoto']);
});
// Route::post('upload/Photo', [PhotosController::class,'uploadPhoto'])->middleware('EnsureToken');
//
