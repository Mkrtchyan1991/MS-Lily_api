<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth; //sa em avelacre


Auth::routes(['verify' => true]);//sa em avelacre


Route::get('/', function () {
    return view('welcome');
});


