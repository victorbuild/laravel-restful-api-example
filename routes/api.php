<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\TypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:api', 'scope:user-info'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('animals', AnimalController::class);
Route::apiResource('types', TypeController::class);
