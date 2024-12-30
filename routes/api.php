<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MideaController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;

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

Route::post('login', [AuthController::class, 'login']);

Route::get('validateToken', [AuthController::class, 'validateToken']);
Route::post('recoverPassword', [UserController::class, 'passwordRecovery']);
Route::post('updatePassword', [UserController::class, 'updatePassword']);


Route::get('validateToken', [AuthController::class, 'validateToken']);

Route::prefix('midea')->group(function(){
    Route::get('code/{code}', [MideaController::class, 'getByCode']);
    Route::post('add-comment', [MideaController::class, 'addComment']);
});

Route::middleware('jwt')->group(function(){

    Route::middleware(AdminMiddleware::class)->group(function() {
        // Middleware do admin
    });

    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('dashboard')->group(function(){
        Route::get('cards', [DashboardController::class, 'cards']);
        Route::get('graphic', [DashboardController::class, 'graphic']);
    });

    Route::prefix('user')->group(function(){
        Route::get('all', [UserController::class, 'all']);
        Route::get('search', [UserController::class, 'search']);
        Route::get('cards', [UserController::class, 'cards']);
        Route::get('me', [UserController::class, 'getUser']);
        Route::post('create', [UserController::class, 'create']);
        Route::patch('{id}', [UserController::class, 'update']);        
        Route::post('block/{id}', [UserController::class, 'userBlock']);
        Route::delete('{id}', [UserController::class, 'delete']);
    });

    Route::prefix('service')->group(function(){
        Route::get('search', [ServiceController::class, 'search']);
        Route::post('create', [ServiceController::class, 'create']);
        Route::patch('{id}', [ServiceController::class, 'update']);
        Route::delete('{id}', [ServiceController::class, 'delete']);
    });

    Route::prefix('client')->group(function(){
        Route::get('search', [ClientController::class, 'search']);
        Route::get('all', [ClientController::class, 'all']);
        Route::post('create', [ClientController::class, 'create']);
        Route::patch('{id}', [ClientController::class, 'update']);
        Route::delete('{id}', [ClientController::class, 'delete']);
    });

    Route::prefix('midea')->group(function(){                
        Route::get('search', [MideaController::class, 'search']);
        Route::post('create', [MideaController::class, 'create']);
        Route::patch('{id}', [MideaController::class, 'update']);
        Route::delete('{id}', [MideaController::class, 'delete']);
    });        
});
