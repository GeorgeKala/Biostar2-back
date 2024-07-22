<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CommandTypeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GetHolidaysController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserTypeController;
use App\Http\Controllers\ForgiveTypesController;
use App\Http\Controllers\ReportController;

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
        Route::get('/departments', [BuildingController::class, 'getBuildingsWithDepartments']);
        Route::post('/', [BuildingController::class, 'store']);
        Route::get('/{building}', [BuildingController::class, 'show']);
        Route::put('/{building}', [BuildingController::class, 'update']);
        Route::delete('/{building}', [BuildingController::class, 'destroy']);
        Route::post('/{building}/attach-department', [BuildingController::class, 'attachDepartments']);
        Route::post('/{building}/detach-department', [BuildingController::class, 'detachDepartments']);
        Route::post('/{building}/update-department', [BuildingController::class, 'updateAttachedDepartments']);
    });
    

    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::get('/nested', [DepartmentController::class, 'nestedDepartments']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
    });


    Route::prefix('schedules')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::get('/{schedule}', [ScheduleController::class, 'show']);
        Route::put('/{schedule}', [ScheduleController::class, 'update']);
        Route::patch('/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/{schedule}', [ScheduleController::class, 'destroy']);
    });


    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store']);
        Route::get('/{group}', [GroupController::class, 'show']);
        Route::put('/{group}', [GroupController::class, 'update']);
        Route::patch('/{group}', [GroupController::class, 'update']);
        Route::delete('/{group}', [GroupController::class, 'destroy']);
    });

    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::patch('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });


    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    Route::prefix('command-types')->group(function () {
        Route::post('/', [CommandTypeController::class, 'store']);
        Route::get('/', [CommandTypeController::class, 'index']);
        Route::get('/{id}', [CommandTypeController::class, 'show']);
        Route::put('/{id}', [CommandTypeController::class, 'update']);
        Route::delete('/{id}', [CommandTypeController::class, 'destroy']);
    });

    Route::get('/holidays', GetHolidaysController::class);

    Route::prefix('forgive-types')->group(function () {
        Route::get('/', [ForgiveTypesController::class, 'index']);
        Route::post('/', [ForgiveTypesController::class, 'store']);
        Route::get('/{forgiveType}', [ForgiveTypesController::class, 'show']);
        Route::put('/{forgiveType}', [ForgiveTypesController::class, 'update']);
        Route::delete('/{forgiveType}', [ForgiveTypesController::class, 'destroy']);
    });

 
    Route::get('/devices', [DeviceController::class, 'fetchDeviceData']);
    Route::post('/devices/{device_id}/scan_card', [DeviceController::class, 'scanCard']);

    Route::post('/make-card', [EmployeeController::class, 'makeCard']);

    Route::post('/reports/login', [ReportController::class, 'login']);
    Route::post('/reports/monthly', [ReportController::class, 'getMonthlyReports']);

    Route::post('/employee-day-detail', [ReportController::class, 'updateOrCreateDayDetail']);
    Route::post('/employee-day-detail/update-day-type', [ReportController::class, 'updateDayTypeForDateRange']);

    Route::post('/fetch-report', [ReportController::class, 'fetchReport']);

    Route::post('/commented-details', [CommentController::class, 'fetchCommentedDetails']);
    Route::post('/employee-orders', [CommentController::class, 'fetchOrders']);

    
});

