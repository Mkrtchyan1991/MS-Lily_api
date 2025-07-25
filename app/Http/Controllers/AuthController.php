<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Rules\InternationalMobilePhone;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;

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
            'mobile_number' => ['sometimes',InternationalMobilePhone::forCreate()],//wenn wir required schreben wollen mussen wir 'name' schreiben,sonst gipt es ein Fehler HTTP 422 in yaak
            'country' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'password' => ['sometimes', 'string', $passwordRule] //sometimes-wenn gips dieses Feld dann validiere es,wenn nicht ist in ordnung
        ]); 


        $passwordHash = Hash::make($request->get('password')); //Wichtig, Hier wird sicher verschlüsselt geschpeichert das Passwort
       //neuen Benutzer erstellen
        $user = User::create([
            'name'     => $request->get('name'),
            'last_name'     => $request->get('last_name'),
            'country'     => $request->get('country'),
            'address'     => $request->get('address'),
            'city'     => $request->get('city'),
            'mobile_number'     => $request->get('mobile_number'),
            'email'    => $request->get('email'),
            'postal_code' => $request->get('postal_code'),
            'password' => $passwordHash,
            'role'     => $request->get('role', 'user') //wenn keine Rolle geschrieben wird,wird automatisch user gesetzt
        ]);
        //für Email verfy
        event(new Registered($user));
        //gpbt eine JSON antwort zurück
        return response()->json(['' => 'User registered'], 201);
    }



    //Funktion für die Anmeldung
//     public function login(Request $request)
// {
//     //Prüft mit Auth::attemp,ob die Zugangsdaten korrekt sind,wenn nicht gipt zurück 401-invalid credentials
//     if (!Auth::attempt($request->only('email', 'password'))) { 
//         return response()->json(['message' => 'Invalid credentials'], 401);
//     }

//     $request->session()->regenerate();
//     //Wenn Anmeldung erfolgreich war
//     $user = Auth::user();//Gipt den aktuell angemeldeten Benutzer zurück
//     //wenn user verfy ist zeigt seine Daten,wenn nicht bringt logaut seite.
//     if (!$user->hasVerifiedEmail()) {
//         Auth::logout();
//         return back()->withErrors(['email' => 'Please verify your email first'])->withInput();
//     }

//     $token = $user->createToken('API Token')->plainTextToken;//erstellt ein neues Token miit laravel Sanctum dann gipt das Token als text zurück
//     //Antwort JSON mit Benutzer und Token
//     return response()->json([
//         'user' => $user,
//         'token' => $token
//     ], 201);
// }

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

    // Store token in session
    Session::put('auth_token', $token);

    return response()->json(['message' => 'Login successful']);
}
    public function logout(Request $request) 
{ 
    // //Aktuelle Token wird gelöscht,Benutzer ist abgemeldet
    // $request->user()->currentAccessToken()->delete();
    // //Bestätigung dass der Benutzer abgemeldet ist
    // return response()->json(['message' => 'Logged out']);

   
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return response()->json(['message' => 'Logged out']);
 
}
}
