<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Student;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentExtensionRequest;
use Carbon\Carbon;

class AssignmentsExtensionController extends Controller
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
    public function requestExtension(Request $request, $assignmentId)
    {
        $request->validate([
            'requested_due_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:1000',
        ]);

        $assignment = Assignment::findOrFail($assignmentId);

        // Check if already expired
        if (!$assignment->is_expired) {
            return response()->json(['message' => 'Assignment is not expired yet.'], 400);
        }

        AssignmentExtensionRequest::create([
            'assignment_id' => $assignment->id,
            'user_id' => auth()->id(),
            'requested_due_date' => $request->requested_due_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Extension request submitted successfully.']);
    }
    /**
     * Store a newly created resource in storage.
     */
  

    /**
     * Show the form for editing the specified resource.
     */
   public function listRequests()
    {
        $requests = AssignmentExtensionRequest::with(['assignment', 'user'])->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $requests]);
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
