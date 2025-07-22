<?php

namespace App\Http\Middleware;

//Anonyme Funktion,die die nächste Middleware darstellt
use Closure;
//die eingehende HTTP-Anfrage
use Illuminate\Http\Request;
//die HTTP-Antwort
use Symfony\Component\HttpFoundation\Response;

//Definiert eine neue Middleware-Klasse namens AdminMiddleware
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    //Dies ist die Hauptmethode, die alle Anfragen verarbeitet, die durch diese Middleware laufen.
    public function handle(Request $request, Closure $next): Response
    {   //wenn user ist keine Admin, lässt nicht rein (feld) gehen
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        //Wenn der Benutzer Admin ist, wird die Anfrage zur nächsten Middleware oder zum Controller weitergeleitet
        return $next($request);
    }
}
