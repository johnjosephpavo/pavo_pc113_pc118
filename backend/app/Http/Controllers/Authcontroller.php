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
                Student::create([
                    'user_id' => $user->id,
                    'first_name' => $request->input('first_name', ''),
                    'last_name' => $request->input('last_name', ''),
                    'age' => $request->input('age', null),
                    'gender' => $request->input('gender', ''),
                    'address' => $request->input('address', ''),
                    'course' => $request->input('course', ''),
                    'contact_number' => $request->input('contact_number', ''),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create student:', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => false,
                    'message' => 'User created, but failed to create in student table: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
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
        \Log::info('Register request received.', $request->all());

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        try {
            // Assign default role (2 for student)
            $validated['role'] = 2;

            \Log::info('Validation successful.', [
                'email' => $validated['email'],
                'role' => $validated['role']
            ]);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $filename = time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
                $path = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');
                $validated['profile_image'] = $path;

                \Log::info('Profile image uploaded.', ['path' => $path]);
            }

            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);

            \Log::info('User created.', ['user_id' => $user->id]);

            // If Student, create Student record
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

            \Log::info('Student record created.', ['student_id' => $student->id]);

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Registration Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }



}
