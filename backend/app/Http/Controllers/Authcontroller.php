<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    
    public function list()
    {
        try {
            $users = User::all();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|integer',
            'profile_image' => 'nullable|image|max:2048',
        ]); 

        // Upload profile image if present
        if ($request->hasFile('profile_image')) {
            $filename = time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
            $path = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');
            $validated['profile_image'] = $path;
        }

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        // If role is 2 (Student), also create a Student record
        if ((int) $user->role === 2) {
            try {
                // Create the student record first
                $student = Student::create([
                    'user_id' => $user->id,
                    'first_name' => $request->input('first_name', ''),
                    'last_name' => $request->input('last_name', ''),
                    'age' => $request->input('age', null),
                    'gender' => $request->input('gender', ''),
                    'address' => $request->input('address', ''),
                    'course' => $request->input('course', ''),
                    'contact_number' => $request->input('contact_number', ''),
                ]);

                // Customize QR Code content (you can use name, ID, etc.)
                $qrData = "Student ID: {$student->id}\nName: {$student->first_name} {$student->last_name}\nCourse: {$student->course}";
                $qrFileName = "qr_codes/student_{$student->id}.png";
                $qrPath = public_path("storage/{$qrFileName}");

                // Generate and save QR Code image
                $svg = QrCode::format('svg')->size(300)->generate($qrData);
                file_put_contents($qrPath = public_path("storage/qr_codes/student_{$student->id}.svg"), $svg);
                // Save the QR code path to the student
                $student->qr_code = "storage/qr_codes/student_{$student->id}.svg";
                $student->save();
                Log::info('Saved QR code to student:', ['qr_code' => $student->qr_code]);
                // Save the QR code path to the student
             

            } catch (\Exception $e) {
                Log::error('Failed to create student:', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => false,
                    'message' => 'User created, but failed to create in student table: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'qr_code_url' => $student->qr_code ? asset($student->qr_code) : null,
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        try {
            Log::info("Update attempt for User ID: {$id}");

            $user = User::findOrFail($id);

            // Log user info before updating
            Log::info("User fetched: ", $user->toArray());

            $validateUser = Validator::make($request->all(), [
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'role' => 'nullable|exists:roles,id',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10048',
                'password' => 'nullable|string|min:6',
            ]);

            if ($validateUser->fails()) {
                Log::error("Validation failed for User ID: {$id}", $validateUser->errors()->toArray());
                return response()->json([
                    'status' => false,
                    'message' => 'Update Failed!',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            $updateData = [];
            $previousRole = $user->role;

            // Log role before update
            Log::info("Previous role: {$previousRole}");

            if ($request->filled('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->filled('role')) {
                $updateData['role'] = $request->role;
            }

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Profile image handling
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $filename);

                if ($user->profile_image && file_exists(public_path('images/' . $user->profile_image))) {
                    unlink(public_path('images/' . $user->profile_image));
                }

                $updateData['profile_image'] = $filename;
            }

            // Log the data to be updated
            Log::info("Update data: ", $updateData);

            // Update user record
            $user->update($updateData);

            // Log the user after update
            Log::info("User after update: ", $user->toArray());

            $newRole = $updateData['role'] ?? $previousRole;

            // Handle role change
            if ($previousRole != 2 && $newRole == 2) {
                Student::create([
                    'user_id' => $user->id,
                    'first_name' => '',
                    'last_name' => '',
                    'age' => null,
                    'gender' => '',
                    'address' => '',
                    'course' => '',
                    'contact_number' => '',
                ]);
                Log::info("New student record created for User ID: {$user->id}");
            }

            if ($previousRole == 2 && $newRole != 2) {
                Student::where('user_id', $user->id)->delete();
                Log::info("Student record deleted for User ID: {$user->id}");
            }

           
            
            // Update student details if still a student
            if ($newRole == 2) {
                try {
                    $student = Student::where('user_id', $user->id)->first();
                    if ($student) {
                        $student->update([
                            'first_name' => $request->input('first_name', $student->first_name),
                            'last_name' => $request->input('last_name', $student->last_name),
                            'age' => $request->input('age', $student->age),
                            'gender' => $request->input('gender', $student->gender),
                            'address' => $request->input('address', $student->address),
                            'course' => $request->input('course', $student->course),
                            'contact_number' => $request->input('contact_number', $student->contact_number),
                        ]);
                        Log::info("Student record updated for User ID: {$user->id}");
                         // Regenerate QR code after update
                        $qrData = "Student ID: {$student->id}\nName: {$student->first_name} {$student->last_name}\nCourse: {$student->course}";
                        $qrFilePath = public_path("storage/qr_codes/student_{$student->id}.svg");

                        // Generate QR code SVG and save
                        $svg = QrCode::format('svg')->size(300)->generate($qrData);
                        file_put_contents($qrFilePath, $svg);

                        // Update QR code path in student record and save
                        $student->qr_code = "storage/qr_codes/student_{$student->id}.svg";
                    }
                } catch (\Throwable $e) {
                    Log::error("Error updating student data for User ID: {$user->id}", ['error' => $e->getMessage()]);
                    return response()->json([
                        'status' => false,
                        'message' => 'User updated but student data failed: ' . $e->getMessage()
                    ], 500);
                }
            }

            Log::info("User update successful for User ID: {$user->id}");

            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            Log::error("Error in updating user: {$th->getMessage()}");
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
    
        return response()->json($user, 200);
    }

   
    public function getUserById($id)
    {
        $user = User::with('student')->find($id); // Eager load the related student data

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'user' => $user,              // includes base user info
            'student' => $user->student   // nullable if not a student
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function login(Request $request)
    {
        // Validate request input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate API Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'User Deleted Successfully!',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }   
    }

    public function getUserProfile(Request $request)
    {
        // Assuming the user is authenticated
        $user = $request->user();

        // Load the student details if the role is 2
        if ($user->role == 2) {
            $user->load('student');
        }

        return response()->json($user);
    }


    public function register(Request $request)
    {
        Log::info('Register request received.', $request->all());

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'profile_image' => 'nullable|image|max:2048',
            // Student fields validation
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:1|max:150',
            'gender' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'course' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
        ]);

        try {
            // Assign default role (2 for student)
            $validated['role'] = 2;

            Log::info('Validation successful.', [
                'email' => $validated['email'],
                'role' => $validated['role']
            ]);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $filename = time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
                $path = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');
                $validated['profile_image'] = $path;

                Log::info('Profile image uploaded.', ['path' => $path]);
            }

            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);

            Log::info('User created.', ['user_id' => $user->id]);

            // Create Student record
            $student = Student::create([
                'user_id' => $user->id,
                'first_name' => $request->input('first_name', ''),
                'last_name' => $request->input('last_name', ''),
                'age' => $request->input('age'),
                'gender' => $request->input('gender', ''),
                'address' => $request->input('address', ''),
                'course' => $request->input('course', ''),
                'contact_number' => $request->input('contact_number', ''),
            ]);

            Log::info('Student record created.', ['student_id' => $student->id]);

            // Generate QR code for new student
            $qrData = "Student ID: {$student->id}\nName: {$student->first_name} {$student->last_name}\nCourse: {$student->course}";
            $qrFilePath = public_path("storage/qr_codes/student_{$student->id}.svg");

            $svg = QrCode::format('svg')->size(300)->generate($qrData);
            file_put_contents($qrFilePath, $svg);

            $student->qr_code = "storage/qr_codes/student_{$student->id}.svg";
            $student->save();

            Log::info("QR code generated for Student ID: {$student->id}");

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'user' => $user,
                'student' => $student,
            ]);

        } catch (\Exception $e) {
            Log::error('Registration Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }



    public function sendResetLink(Request $request)
    {
        try {
            Log::info('Password reset request received', ['email' => $request->email]);

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                Log::warning('Password reset validation failed', ['errors' => $validator->errors()]);
                return response()->json(['status' => false, 'message' => 'Invalid email address'], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            $token = Password::createToken($user);

            Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

            Log::info('Custom password reset email sent', ['email' => $request->email]);

            return response()->json(['status' => true, 'message' => 'Reset link sent to your email.']);

        } catch (\Exception $e) {
            Log::error('Exception occurred while sending password reset link', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while sending reset link.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function resetPassword(Request $request)
    {
        try {
            Log::info('Password reset attempt', ['email' => $request->email]);

            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                Log::warning('Password reset validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password)
                    ])->save();

                    Log::info('User password reset successfully', ['user_id' => $user->id]);
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json(['status' => true, 'message' => 'Password reset successfully.']);
            } else {
                Log::error('Password reset failed', ['status' => $status]);
                return response()->json(['status' => false, 'message' => __($status)], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception occurred during password reset', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while resetting password.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function sendEmail($toEmail, $toName, $subject, $htmlBody)
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
            $mail->setFrom('jj.pavo@mlgcl.edu.ph', 'Task Submission System');
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            $mail->send();
            Log::info("Email sent successfully to {$toEmail} with subject: {$subject}");
        } catch (Exception $e) {
            Log::error("Email could not be sent to {$toEmail}. Mailer Error: {$mail->ErrorInfo}");
        }
    }

}
