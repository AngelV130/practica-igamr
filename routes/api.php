<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthVerifySessionController;
use App\Http\Controllers\Auth\AuthVerifyUserController;
use App\Http\Controllers\User\UserController;
use App\Models\User;

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


Route::controller(AuthVerifySessionController::class)->group(function () {
    Route::post('register', 'register');

    Route::post('login', 'login')->name('login');

    Route::post('logout', 'lgout')->name('logout')->middleware('auth:sanctum');

});

Route::controller(AuthVerifyUserController::class)->group(function () {
    Route::get('verify/email/{id}','activeAccount')->name('active.account')->whereNumber('id')->middleware('signed');
    
    Route::post('verify/code/{id}', 'verifyCode')->name('verify.code')->whereNumber('id')->middleware('signed');
});


Route::group(['middleware' => ['auth:sanctum']],function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'users')->name('users.info')->middleware('rol:1');

        Route::get('perfil', 'perfil')->name('perfil.info');
    });
});



/**
 * Otras Rutas
 */
 Route::get('', function () {
    
    return response()->json([
        'message' => 'Unauthenticated',
        'status' => 401,
    ], 401);
})->name('unauthenticated');
