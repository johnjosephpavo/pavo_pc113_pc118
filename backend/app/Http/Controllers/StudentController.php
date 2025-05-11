<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function list()
    {
        try {
            $students = Student::all();
            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   

    public function store(Request $request): JsonResponse
    {
  
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id): JsonResponse
    {
        Log::info("Update student attempt for ID: $id", $request->all());

        $student = Student::find($id);

        if (!$student) {
            Log::warning("Student not found: $id");
            return response()->json(['message' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'address' => 'required|string',
            'course' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            Log::error("Validation failed", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'age' => $request->age,
            'gender' => $request->gender,
            'address' => $request->address,
            'course' => $request->course,
            'contact_number' => $request->contact_number,
        ]);

        Log::info("Student updated successfully for ID: $id");

        return response()->json([
            'status' => true,
            'message' => 'Student updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        
    }

    public function edit($id)
    {
        $student = Student::find($id);
    
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Student not found'], 404);
        }
    
        return response()->json($student, 200);
    }
    

     public function getStudentById($id)
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

}