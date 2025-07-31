<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
   /**
    * Display a listing of users with pagination
    */
   public function index(Request $request)
   {
      $query = User::query();

      // Search functionality
      if ($request->has('search')) {
         $search = $request->search;
         $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
               ->orWhere('email', 'like', "%{$search}%")
               ->orWhere('last_name', 'like', "%{$search}%");
         });
      }

      // Filter by role
      if ($request->has('role')) {
         $query->where('role', $request->role);
      }

      // Filter by email verification status
      if ($request->has('verified')) {
         if ($request->verified == '1') {
            $query->whereNotNull('email_verified_at');
         } else {
            $query->whereNull('email_verified_at');
         }
      }

      $users = $query->latest()->paginate(10);

      return response()->json($users);
   }

   /**
    * Display the specified user
    */
   public function show($id)
   {
      $user = User::findOrFail($id);
      return new UserResource($user);
   }

   /**
    * Store a newly created user
    */
   public function store(Request $request)
   {
      $request->validate([
         'name' => 'required|string|max:255',
         'last_name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:users',
         'password' => 'required|string|min:8|confirmed',
         'role' => 'nullable|string|in:user,admin',
         'country' => 'nullable|string|max:100',
         'address' => 'nullable|string|max:100',
         'city' => 'nullable|string|max:100',
         'postal_code' => 'nullable|string|max:10',
         'mobile_number' => 'nullable|string|max:15',
      ]);

      $user = User::create([
         'name' => $request->name,
         'last_name' => $request->last_name,
         'email' => $request->email,
         'password' => Hash::make($request->password),
         'role' => $request->role ?? 'user',
         'country' => $request->country,
         'address' => $request->address,
         'city' => $request->city,
         'postal_code' => $request->postal_code,
         'mobile_number' => $request->mobile_number,
      ]);

      return response()->json([
         'message' => 'User created successfully!',
         'user' => new UserResource($user)
      ], 201);
   }

   /**
    * Update the specified user
    */
   public function update(Request $request, $id)
   {
      $user = User::findOrFail($id);

      $request->validate([
         'name' => 'required|string|max:255',
         'last_name' => 'required|string|max:255',
         'email' => "required|string|email|max:255|unique:users,email,{$user->id}",
         'password' => 'nullable|string|min:8|confirmed',
         'role' => 'nullable|string|in:user,admin',
         'country' => 'nullable|string|max:100',
         'address' => 'nullable|string|max:100',
         'city' => 'nullable|string|max:100',
         'postal_code' => 'nullable|string|max:10',
         'mobile_number' => 'nullable|string|max:15',
      ]);

      $user->name = $request->name;
      $user->last_name = $request->last_name;
      $user->email = $request->email;
      $user->role = $request->role ?? $user->role;
      $user->country = $request->country;
      $user->address = $request->address;
      $user->city = $request->city;
      $user->postal_code = $request->postal_code;
      $user->mobile_number = $request->mobile_number;

      // Only update password if provided
      if ($request->filled('password')) {
         $user->password = Hash::make($request->password);
      }

      $user->save();

      return response()->json([
         'message' => 'User updated successfully!',
         'user' => new UserResource($user)
      ]);
   }

   /**
    * Remove the specified user from storage
    */
   public function destroy($id)
   {
      $user = User::findOrFail($id);

      // Prevent admin from deleting themselves
      if ($user->id === auth()->id()) {
         return response()->json([
            'message' => 'You cannot delete your own account'
         ], 403);
      }

      $user->delete();

      return response()->json([
         'message' => 'User deleted successfully!'
      ]);
   }

   /**
    * Toggle user role between 'user' and 'admin'
    */
   public function toggleRole($id)
   {
      $user = User::findOrFail($id);

      // Prevent admin from changing their own role
      if ($user->id === auth()->id()) {
         return response()->json([
            'message' => 'You cannot change your own role'
         ], 403);
      }

      $user->role = $user->role === 'admin' ? 'user' : 'admin';
      $user->save();

      return response()->json([
         'message' => "User role changed to {$user->role}",
         'user' => new UserResource($user)
      ]);
   }

   /**
    * Get user statistics for admin dashboard
    */
   public function statistics()
   {
      $totalUsers = User::count();
      $adminUsers = User::where('role', 'admin')->count();
      $regularUsers = User::where('role', 'user')->count();
      $verifiedUsers = User::whereNotNull('email_verified_at')->count();
      $unverifiedUsers = User::whereNull('email_verified_at')->count();
      $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();

      return response()->json([
         'total_users' => $totalUsers,
         'admin_users' => $adminUsers,
         'regular_users' => $regularUsers,
         'verified_users' => $verifiedUsers,
         'unverified_users' => $unverifiedUsers,
         'recent_users' => $recentUsers,
      ]);
   }

   /**
    * Verify a user's email manually
    */
   public function verifyEmail($id)
   {
      $user = User::findOrFail($id);

      if ($user->hasVerifiedEmail()) {
         return response()->json([
            'message' => 'User email is already verified'
         ], 400);
      }

      $user->markEmailAsVerified();

      return response()->json([
         'message' => 'User email verified successfully!',
         'user' => new UserResource($user)
      ]);
   }

   /**
    * Suspend or unsuspend a user
    */
   public function toggleSuspension($id)
   {
      $user = User::findOrFail($id);

      // Prevent admin from suspending themselves
      if ($user->id === auth()->id()) {
         return response()->json([
            'message' => 'You cannot suspend your own account'
         ], 403);
      }

      // Assuming you have a 'suspended_at' field in your users table
      if ($user->suspended_at) {
         $user->suspended_at = null;
         $message = 'User unsuspended successfully!';
      } else {
         $user->suspended_at = now();
         $message = 'User suspended successfully!';
      }

      $user->save();

      return response()->json([
         'message' => $message,
         'user' => new UserResource($user)
      ]);
   }
}