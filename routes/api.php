<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::PUT('update-fcm-token', 'updateFcmToken');
        Route::PUT('update-user-location', 'updateUserLocation');
        Route::POST('change-password', 'changePassword');
        Route::POST('logout', 'logout');
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::POST('add-inventory', 'add_inventory');
        Route::GET('inventories', 'inventories');

        

    }); 
});

Route::prefix('auth')->group(function() {
    Route::controller(AuthController::class)->group(function () {
        Route::POST('login', 'login');
        Route::POST('register', 'register');
        Route::POST('verify-token', 'verifyToken');
        Route::POST('resend-otp-token', 'resendOtpToken');
        Route::POST('forgot-password', 'forgotPassword');
        Route::PUT('set-password', 'setPassword');
        Route::GET('unauthenticated', 'unauthenticatedUser')->name('api.unauthenticated');
    });
});
