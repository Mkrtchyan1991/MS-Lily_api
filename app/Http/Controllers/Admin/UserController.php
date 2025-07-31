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
   public function index()
   {
      $users = User::latest()->paginate(10);
      return response()->json($users);
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
         'email' => 'required|string|email|max:255|unique:users',
         'password' => 'required|string|min:8',
         'role' => 'nullable|string|in:admin,user',
      ]);

      $user = User::create([
         'name' => $request->name,
         'email' => $request->email,
         'password' => bcrypt($request->password),
         'role' => $request->role ?? 'user',
      ]);

      return response()->json(['message' => 'User created successfully!', 'user' => $user], 201);
   }

   /**
    * Update the specified user
    */
   public function update(Request $request, $id)
   {
      $user = User::findOrFail($id);

      $request->validate([
         'name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
         'role' => 'nullable|string|in:admin,user',
      ]);

      $user->update([
         'name' => $request->name,
         'email' => $request->email,
         'role' => $request->role ?? $user->role,
      ]);

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