<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
   /**
    * Display a listing of users
    */
   public function index(Request $request)
   {
      $query = User::latest();

      if ($request->filled('role')) {
         $query->where('role', $request->role);  // filter by role
      }

      $perPage = $request->get('per_page', 10);
      $perPage = min(max($perPage, 1), 100);

      return response()->json($query->paginate($perPage));
   }

   /**
    * Display the specified user
    */
   public function show($id)
   {
      $user = User::findOrFail($id);
      return response()->json($user);
   }

   /**
    * Store a newly created user
    */
   public function store(Request $request)
   {
      $request->validate([
         'name' => 'required|string|max:255',
         'last_name' => 'nullable|string|max:255',
         'email' => 'required|string|email|max:255|unique:users',
         'mobile_number' => 'nullable|string|max:255',
         'password' => 'required|string|min:8|confirmed',
         'role' => 'nullable|string|in:admin,user',
         'country' => 'nullable|string|max:255',
         'address' => 'nullable|string|max:255',
         'city' => 'nullable|string|max:255',
         'postal_code' => 'nullable|string|max:255',
      ]);

      $data = $request->only([
         'name',
         'last_name',
         'email',
         'mobile_number',
         'role',
         'country',
         'address',
         'city',
         'postal_code',
      ]);

      $data['password'] = bcrypt($request->password);
      $data['role'] = $request->role ?? 'user';

      $user = User::create($data);

      return response()->json(['message' => 'User created successfully!', 'user' => $user], 201);
   }

   /**
    * Update the specified user
    */
   public function update(Request $request, $id)
   {
      $user = User::findOrFail($id);

      $request->validate([
         'name' => 'sometimes|required|string|max:255',
         'last_name' => 'sometimes|nullable|string|max:255',
         'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
         'mobile_number' => 'sometimes|nullable|string|max:255',
         'role' => 'sometimes|string|in:admin,user',
         'country' => 'sometimes|nullable|string|max:255',
         'address' => 'sometimes|nullable|string|max:255',
         'city' => 'sometimes|nullable|string|max:255',
         'postal_code' => 'sometimes|nullable|string|max:255',
      ]);

      $user->update($request->only([
         'name',
         'last_name',
         'email',
         'mobile_number',
         'role',
         'country',
         'address',
         'city',
         'postal_code',
      ]));

      return response()->json(['message' => 'User updated successfully!', 'user' => $user]);
   }

   /**
    * Remove the specified user
    */
   public function destroy($id)
   {
      $user = User::findOrFail($id);
      $user->delete();

      return response()->json(['message' => 'User deleted successfully!']);
   }
}