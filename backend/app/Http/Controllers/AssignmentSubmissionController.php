<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
   public function submitAssignment(Request $request, $assignmentId)
    {
        try {
            Log::info('submitAssignment called', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $request->validate([
                'answer' => 'required|string',
            ]);

            // Check if the student is allowed to submit for this assignment
            $assignment = Assignment::findOrFail($assignmentId);

            if ($assignment->assigned_to != Auth::id()) {
                return response()->json(['status' => false, 'message' => 'You are not assigned to this assignment.'], 403);
            }

            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignmentId,
                'user_id' => Auth::id(),
                'answer' => $request->answer,
                'submitted_at' => now(),
            ]);

            Log::info('Assignment submitted', ['submission_id' => $submission->id]);

            return response()->json(['status' => true, 'message' => 'Assignment submitted successfully.']);
            
        } catch (\Exception $e) {
            Log::error('Failed to submit assignment', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to submit assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
   public function viewSubmissions($assignmentId)
    {
        try {
            $assignment = Assignment::findOrFail($assignmentId);

            // Check if the user is allowed to view this assignment's submissions
            if (Auth::id() != $assignment->assigned_by && Auth::id() != $assignment->assigned_to) {
                return response()->json(['status' => false, 'message' => 'You are not authorized to view these submissions.'], 403);
            }

            $submissions = AssignmentSubmission::where('assignment_id', $assignmentId)->get();

            return response()->json(['status' => true, 'submissions' => $submissions]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch submissions', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch submissions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAssignmentsList()
    {
        try {
            // Assuming current user is a student
            $assignments = Assignment::where('assigned_to', auth()->id())->get();

            return response()->json($assignments);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch assignments.',
                'error' => $e->getMessage()
            ], 500);
        }
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
