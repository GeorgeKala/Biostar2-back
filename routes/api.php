<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DepartmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserTypeController;
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




Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('user-types')->group(function () {
        Route::get('/', [UserTypeController::class, 'index']);
        Route::post('/', [UserTypeController::class, 'store']);
        Route::get('/{user_type}', [UserTypeController::class, 'show']);
        Route::put('/{user_type}', [UserTypeController::class, 'update']);
        Route::delete('/{user_type}', [UserTypeController::class, 'destroy']);
    });
    
    Route::prefix('buildings')->group(function () {
        Route::get('/', [BuildingController::class, 'index']);
        Route::post('/', [BuildingController::class, 'store']);
        Route::get('/{building}', [BuildingController::class, 'show']);
        Route::put('/{building}', [BuildingController::class, 'update']);
        Route::delete('/{building}', [BuildingController::class, 'destroy']);
        Route::post('/{building}/attach-department', [BuildingController::class, 'attachDepartments']);
        Route::post('/{building}/detach-department', [BuildingController::class, 'detachDepartments']);
    });
    
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
    });
   
});

