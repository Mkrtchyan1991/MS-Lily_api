<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use App\Rules\InternationalMobilePhone;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(Request $request) //zugriff auf Folmulardaten
    {
        //passwort validation
        $passwordRule = Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();

        //validation
        $request->validate([
            'name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|string|email|max:255|unique:users,email',
            'mobile_number' => ['sometimes', InternationalMobilePhone::forCreate()],//wenn wir required schreben wollen mussen wir 'name' schreiben,sonst gipt es ein Fehler HTTP 422 in yaak
            'country' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'password' => ['sometimes', 'string', $passwordRule] //sometimes-wenn gips dieses Feld dann validiere es,wenn nicht ist in ordnung
        ]);

        $passwordHash = Hash::make($request->get('password')); //Wichtig, Hier wird sicher verschlüsselt geschpeichert das Passwort

        //neuen Benutzer erstellen
        $user = User::create([
            'name' => $request->get('name'),
            'last_name' => $request->get('last_name'),
            'country' => $request->get('country'),
            'address' => $request->get('address'),
            'city' => $request->get('city'),
            'mobile_number' => $request->get('mobile_number'),
            'email' => $request->get('email'),
            'postal_code' => $request->get('postal_code'),
            'password' => $passwordHash,
            'role' => $request->get('role', 'user') //wenn keine Rolle geschrieben wird,wird automatisch user gesetzt
        ]);

        //für Email verfy
        event(new Registered($user));

        //gpbt eine JSON antwort zurück
        return response()->json(['message' => 'User registered'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return response()->json(['message' => 'Please verify your email first'], 403);
        }

        // Create Sanctum token
        $token = $user->createToken('API Token')->plainTextToken;

        // Log successful login
        Log::info('User login', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        // Return token and user data to frontend
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // Handle Sanctum token-based logout (API requests)
        if ($user && $user->currentAccessToken()) {
            // Log the token-based logout before deletion
            Log::info('Token-based logout', [
                'user_id' => $user->id,
                'token_name' => $user->currentAccessToken()->name,
                'ip' => $request->ip()
            ]);

            // Delete the current access token
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        }

        return response()->json([
            'message' => 'No active token found'
        ], 401);
    }

    /**
     * Logout from all devices (revoke all tokens)
     * Optional additional method for enhanced security
     */
    public function logoutAllDevices(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No authenticated user found'
            ], 401);
        }

        // Log the action
        Log::info('Logout from all devices', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'token_count' => $user->tokens()->count()
        ]);

        // Revoke all tokens for this user
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ], 200);
    }
}