<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function studentD()
    {
        return view('student.dashboard');
    }

    public function list()
    {
        return response()->json(Student::all());
    }

   

   public function store(Request $request): JsonResponse
{
    try {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'age' => 'required|integer|min:1',
            'gender' => 'required|in:Male,Female,male,female',
            'address' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // user_id if user who logged in
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $student = Student::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Student created successfully',
            'student' => $student
        ], 201);

    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function search(Request $request)
    {
        $search = $request->query('search');
    
        if ($search) {
            $students = Student::where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->get();
    
            if ($students->isNotEmpty()) {
                return response()->json($students);
            } else {
                return response()->json(['message' => 'Student not found'], 404);
            }
        }
    
        $students = Student::all();
        return response()->json($students);
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $student = Student::find($id); // Correct model and variable name

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404); // Corrected message
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'age' => 'integer',
            'gender' => 'string',
            'course' => 'string|max:255', // Adjusted field to match Student model
            'contact_number' => 'string|max:15', // Adjusted field to match Student model
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Student updated successfully',
            'student' => $student,
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Student Deleted Successfully!',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }   
    }

    public function edit($id)
    {
        $student = Student::find($id);
    
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Student not found'], 404);
        }
    
        return response()->json($student, 200);
    }
    

}