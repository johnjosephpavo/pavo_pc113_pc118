<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'role' => 'required|string|max:2',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'password' => 'required|string|min:6',
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Create Failed!',
                    'errors' => $validateUser->errors()
                ], 422);
            }
    
            // Handle profile image upload
            $filename = null;
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $filename);
            }
            $imageUrl = $filename ? url('images/' . $filename) : null;
    
            // Create user
            $user = User::create([
                'email' => $request->email,
                'role' => $request->role,
                'profile_image' => $filename,
                'password' => Hash::make($request->password),
            ]);
    
            // Disable cache for the response
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'image_url' => $imageUrl,
                'user' => $user,
            ], 201)->header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $th->getMessage()
            ], 500);
        }
    }
    


    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
    
            $validateUser = Validator::make($request->all(), [
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'role' => 'nullable|exists:roles,id',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10048',
                'password' => 'nullable|string|min:6',
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Update Failed!',
                    'errors' => $validateUser->errors()
                ], 422);
            }
    
            $updateData = [];
    
            if ($request->filled('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->filled('role')) {
                $updateData['role'] = $request->role;
            }
    
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
    
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $filename);
    
                // Delete old image if exists
                if ($user->profile_image && file_exists(public_path('images/' . $user->profile_image))) {
                    unlink(public_path('images/' . $user->profile_image));
                }
    
                $updateData['profile_image'] = $filename;
            }
    
            // Apply updates
            $user->update($updateData);
    
            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'user' => $user
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
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
    
        return response()->json($user, 200);
    }



    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
   
    public function getUserById($id)
    {
        $user = User::find($id); // find() returns null if not found
    
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        return response()->json([
            'status' => true,
            'user' => $user,
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

    

}
