<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Student;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;

use Illuminate\Http\Request;

class AssignmentSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function submit(Request $request)
    {
        try {
            $request->validate([
                'assignment_id' => 'required|exists:assignments,id',
                'answer' => 'nullable|string',
            ]);

            $userId = auth()->id();
            Log::info("User $userId started assignment submission", ['assignment_id' => $request->assignment_id]);

            AssignmentSubmission::create([
                'assignment_id' => $request->assignment_id,
                'user_id' => $userId,
                'answer' => $request->answer,
            ]);

            // Update assignment status to 'Submitted' (1)
            Assignment::where('id', $request->assignment_id)->update(['status' => 1]);
            Log::info("Assignment marked as submitted", ['assignment_id' => $request->assignment_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Assignment submitted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Assignment submission failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit assignment. Please try again.',
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    
    public function viewAssignments()
    {
        $user = auth()->user();

        if ($user->role == 1) {
            // Admin: show all assignments (or only ones they assigned if you prefer)
            $assignments = Assignment::with(['user', 'student.student'])->get();
        } else {
            // Student: show only their assigned assignments
            $assignments = Assignment::with(['user', 'student.student'])
                            ->where('assigned_to', $user->id)
                            ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $assignments
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
}
