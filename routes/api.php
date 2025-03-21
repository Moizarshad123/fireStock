<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ManagerController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::PUT('update-fcm-token', 'updateFcmToken');
        Route::PUT('update-user-location', 'updateUserLocation');
        Route::POST('change-password', 'changePassword');
        Route::POST('logout', 'logout');
        Route::POST('support', 'support');
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::GET('inventories', 'inventories');
        Route::POST('add-inventory', 'add_inventory');
        Route::POST('update-inventory', 'update_inventory');
        Route::DELETE('delete-inventory', 'deleteInventory');
    }); 

    Route::controller(PaymentController::class)->group(function () {
        Route::POST('add-payment', 'addPayment');
        Route::POST('update-payment', 'updatePayment');
        Route::GET('payments', 'payments');
    }); 

    Route::controller(MemberController::class)->group(function () {
        Route::GET('member-dashboard', 'memberDashboard');
        Route::POST('join-request', 'joinRequest');
        Route::GET('stations', 'stations');

    });
    
    Route::controller(ManagerController::class)->group(function () {
        Route::GET('dashboard', 'managerDashboard');
        Route::POST('add-member', 'addMember');
        Route::POST('edit-station', 'editStation');
        Route::GET('notifications', 'notifications');
        Route::GET('mark-as-read', 'markAsRead');
        Route::GET('members', 'members');
        Route::GET('remove-member', 'removeMember');
        Route::GET('station-requests', 'stationRequests');
        Route::POST('update-request-status', 'updateRequestStatus');
        
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
