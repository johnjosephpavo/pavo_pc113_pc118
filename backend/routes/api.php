<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssignmentController;


// Route::middleware(['auth:sanctum', 'allow.roles:1,2,3'])->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/admin-dashboard', [StudentController::class, 'index']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);


    
    Route::middleware('auth:sanctum')->group(function(){ 
        
        // Users //    
        Route::get('/usersList', [AuthController::class, 'list']); 
        Route::post('/createusers', [AuthController::class, 'store']);
        Route::get('/get/users/{id}', [AuthController::class, 'edit']);
        Route::get('/getUserById/{id}', [AuthController::class, 'getUserById']);
        Route::put('/update/users/{id}', [AuthController::class, 'updateUser']);
        Route::post('/update/users/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/delete/users/{id}', [AuthController::class, 'destroy']);

        // Students //
        Route::get('/studentsList', [StudentController::class, 'list']);  
        Route::get('/getStudentById/{id}', [StudentController::class, 'getStudentById']);
        Route::put('/update/students/{id}', [StudentController::class, 'update']); 
        Route::post('/update/students/{id}', [StudentController::class, 'update']); 
        Route::get('/get/students/{id}', [StudentController::class, 'edit']);

        // Assignments //
        Route::get('/assignmentsList', [AssignmentController::class, 'list']); 
        Route::post('/createAssignment', [AssignmentController::class, 'createAssignment']);
        Route::get('/getAssignmentById/{id}', [AssignmentController::class, 'getAssignmentById']);
        Route::put('/update/assignments/{id}', [AssignmentController::class, 'update']); 
        Route::post('/update/assignments/{id}', [AssignmentController::class, 'update']); 
        Route::get('/get/assignments/{id}', [AssignmentController::class, 'edit']);
        Route::get('/getStudents', [AssignmentController::class, 'getStudents']);
        Route::get('/getAdmins', [AssignmentController::class, 'getAdmins']);
        Route::delete('/delete/assignments/{id}', [AssignmentController::class, 'destroy']);

        
    });



    // Route::get('/employeesList', [EmployeeController::class, 'list']);
    // Route::post('/createemployees', [EmployeeController::class, 'store']);
    // Route::get('/employees/search', [EmployeeController::class, 'search']);
    // Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    // Route::delete('/delete/employees/{id}', [EmployeeController::class, 'destroy']);
    // Route::get('/get/employees/{id}', [EmployeeController::class, 'edit']);


    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::middleware('auth:sanctum')->put('/update/student/{id}', [StudentController::class, 'update']);

    });

// });