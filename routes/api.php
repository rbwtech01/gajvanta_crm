<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MachineryItemController;
use App\Http\Controllers\Api\SerialVerificationController;
use App\Http\Middleware\BajajApiAuthentication;


/*
|--------------------------------------------------------------------------
| Bajaj Integration APIs
|--------------------------------------------------------------------------
*/

Route::middleware(BajajApiAuthentication::class)->group(function () {

    // Get all active machinery items
    Route::get('/machinery-items', [
        MachineryItemController::class,
        'index'
    ]);

    // Get a specific machinery item
    Route::get('/machinery-items/{machineryItem}', [
        MachineryItemController::class,
        'show'
    ]);

    // Verify machinery serial number
    Route::post('/serials/verify', [
        SerialVerificationController::class,
        'verify'
    ]);

}); 
