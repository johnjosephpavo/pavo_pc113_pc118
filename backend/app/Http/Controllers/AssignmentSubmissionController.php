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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function viewAssignments()
    {
        // \Log::info('viewAssignments endpoint hit by user ID: ' . auth()->id());

        $assignments = Assignment::with(['user', 'student.student'])
                        ->where('assigned_to', auth()->id())
                        ->get();

        // \Log::info('Assignments fetched:', $assignments->toArray());

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
