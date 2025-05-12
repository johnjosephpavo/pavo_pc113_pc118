<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\Assignment;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        try {
            $assignments = Assignment::with(['user', 'student'])->get();
            return response()->json($assignments);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createAssignment(Request $request)
    {
        try {
            Log::info('createAssignment called', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
            ]);

            // Optional Role check — only uncomment if sure role is an integer:
            // if (auth()->user()->role !== 1) {
            //     Log::warning('Unauthorized attempt to create assignment', ['user_id' => auth()->id()]);
            //     return response()->json(['message' => 'Unauthorized'], 403);
            // }

            $assignment = Assignment::create([
                'assigned_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
            ]);

            Log::info('Assignment created', ['assignment_id' => $assignment->id]);

            return response()->json(['status' => true, 'message' => 'Assignment created.']);
            
        } catch (\Exception $e) {
            Log::error('Failed to create assignment', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
     public function edit($id)
    {
        $assignment = Assignment::find($id);
    
        if (!$assignment) {
            return response()->json(['status' => false, 'message' => 'Assignment not found'], 404);
        }
    
        return response()->json($assignment, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getAssignmentById($id)
    {
        $student = Student::with('user')->find($id); // loads student and user
    
        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found'
            ], 404);
        }
    
        return response()->json([
            'status' => true,
            'student' => $student,
            'user' => $student->user, //include user
        ], 200);
    }

    public function getStudents()
    {
        try {
            $students = DB::table('users')
                ->join('students', 'users.id', '=', 'students.user_id')
                ->where('users.role', 2) // assuming role_id links to roles table
                ->select('users.id', 'students.first_name', 'students.last_name')
                ->get();

            return response()->json(['students' => $students], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAdmins()
    {
        try {
            // Fetch all users with role 1 (Admin), including the currently logged-in user if needed
            $admins = User::where('role', 1)->get();

            return response()->json([
                'status' => true,
                'admins' => $admins
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch admins.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
