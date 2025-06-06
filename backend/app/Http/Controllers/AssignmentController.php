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
use App\Models\AssignmentExtensionRequest;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        try {
            // $assignments = Assignment::with(['user', 'student'])->get();
            // $assignments = Assignment::with(['student.student', 'extensionRequests.user'])->get();
            $assignments = Assignment::with(['student.student', 'extensionRequests.user', 'extensionRequests.student'])->get();

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

            $assignment = Assignment::create([
                'assigned_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => 0,
            ]);

            Log::info('Assignment created', ['assignment_id' => $assignment->id]);

            // Send email
            $recipient = User::find($request->assigned_to);
            if ($recipient) {
                $this->sendAssignmentEmail($recipient->email, $recipient->student->first_name ?? 'Student', $assignment);
            }

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

    protected function sendAssignmentEmail($toEmail, $toName, $assignment)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jj.pavo@mlgcl.edu.ph'; 
            $mail->Password   = 'dypadlwmytbkfqdt'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('jj.pavo@mlgcl.edu.ph', 'Assignment Portal');
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Assignment: ' . $assignment->title;
            $mail->Body    = "
                <p>Hello {$toName},</p>
                <p>You have been assigned a new assignment titled <strong>{$assignment->title}</strong>.</p>
                <p><strong>Due Date:</strong> {$assignment->due_date}</p>
                <p><strong>Description:</strong><br>{$assignment->description}</p>
                <p>Please log in to your account to view more details.</p>
                <br><p>Regards,<br><strong>Task Submission System</strong></p>
            ";

            $mail->send();
            Log::info('Assignment email sent to ' . $toEmail);
        } catch (Exception $e) {
            Log::error("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $assignment = Assignment::with(['extensionRequests.student'])->findOrFail($id);

            return response()->json([
                'status' => true,
                'extension_requests' => $assignment->extensionRequests
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
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
    public function updateAssignment(Request $request, string $id)
    {
        try {
            Log::info('updateAssignment called', [
                'user_id' => auth()->id(),
                'assignment_id' => $id,
                'request_data' => $request->all()
            ]);

            $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
            ]);

            $assignment = Assignment::findOrFail($id);

            // Optional: check if current user is authorized to update
            // if ($assignment->assigned_by !== auth()->id()) {
            //     return response()->json(['message' => 'Unauthorized'], 403);
            // }

            $assignment->update([
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
            ]);

            Log::info('Assignment updated', ['assignment_id' => $assignment->id]);

            return response()->json(['status' => true, 'message' => 'Assignment updated.']);
            
        } catch (\Exception $e) {
            Log::error('Failed to update assignment', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'assignment_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to update assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $assignment = Assignment::findOrFail($id);
            $assignment->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Assignment Deleted Successfully!',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }   
    }

    public function getAssignmentById($id)
    {
        $assignment = Assignment::with(['user', 'student.student', 'extensionRequests.user'])->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => false,
                'message' => 'Assignment not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'assignment' => $assignment,
            'user' => $assignment->user,
            'extension_requests' => $assignment->extensionRequests // include for frontend
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

    // API Helper
    function apiResponse($status, $message, $data = [])
    {
        return response()->json(array_merge([
            'status' => $status,
            'message' => $message,
        ], $data));
    }


    public function approveExtension($requestId)
    {
        $request = AssignmentExtensionRequest::findOrFail($requestId);

        $request->status = 'approved';
        $request->save();

        // $assignment = $request->assignment;
        // $assignment->due_date = $request->requested_due_date;
        // $assignment->save();

        return response()->json(['status' => true, 'message' => 'Extension approved.']);
    }
    /**
     * Display the specified resource.
     */
    public function denyExtension($requestId)
    {
        $request = AssignmentExtensionRequest::findOrFail($requestId);
        $request->status = 'rejected';
        $request->save();

        return response()->json(['status' => true, 'message' => 'Extension rejected.']);
    }

   

}
