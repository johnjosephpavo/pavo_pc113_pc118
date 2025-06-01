<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\AssignmentsExtensionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;


    // Get authenticated user //
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        // \Log::info('Authenticated user:', [auth()->user()]);
        return $request->user();
    });
    
    // Get user with student //
    Route::middleware('auth:sanctum')->get('/userRole', function (Request $request) {
        return $request->user()->load('student'); 
    });

    // Get all Data //
    Route::middleware('auth:sanctum')->get('/dashboard-stats', function () {
        return response()->json([
            'users' => User::count(),
            'students' => Student::count(),
            'assignments' => Assignment::count(),
            'submissions' => AssignmentSubmission::count(),
        ]);
    });

    // Dashboard Controller//
    Route::get('/activities/recent-submissions', [DashboardController::class, 'recentSubmissions']);

    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);



    
    Route::middleware('auth:sanctum')->group(function(){ 
        
        // Users //    
        Route::get('/usersList', [AuthController::class, 'list']); 
        Route::get('/getUserProfile', [AuthController::class, 'getUserProfile']); 
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
        Route::put('/update/assignments/{id}', [AssignmentController::class, 'updateAssignment']); 
        Route::post('/update/assignments/{id}', [AssignmentController::class, 'updateAssignment']); 
        Route::get('/get/assignments/{id}', [AssignmentController::class, 'edit']);
        Route::get('/getStudents', [AssignmentController::class, 'getStudents']);
        Route::delete('/delete/assignments/{id}', [AssignmentController::class, 'destroy']);
        Route::get('/show/assignments/{id}', [AssignmentController::class, 'show']);
        Route::post('/extension-requests/{id}/approve', [AssignmentController::class, 'approveExtension']);
        Route::post('/extension-requests/{id}/deny', [AssignmentController::class, 'denyExtension']);


        // Request Extension //
        Route::post('/assignments/{id}/request-extension', [AssignmentsExtensionController::class, 'requestExtension']);
    
      
    
            // Submit Assignments //
            Route::post('/assignment/submit', [AssignmentSubmissionController::class, 'submit']);
            
            // View Assignments fron role 2 (students) //
            Route::get('/get/viewAssignments', [AssignmentSubmissionController::class, 'viewAssignments']);

            // Export Students //
            Route::get('/students/export-pdf', [StudentController::class, 'exportPDF']);

       


    }); 

   

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

