<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {
    Route::middleware(['auth:sanctum', 'throttle:api'])->get('/user', function (Request $request) {
        return $request->user();
    });
});