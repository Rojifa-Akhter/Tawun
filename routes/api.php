<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//auth route
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot_password', [AuthController::class, 'forgotPassword']);
    Route::post('reset_password', [AuthController::class, 'resetPassword']);
    Route::post('resend_otp', [AuthController::class, 'resendOtp']);
    Route::middleware('auth:api')->group(function () {
        Route::get('own_profile', [AuthController::class, 'ownProfile']);
        Route::post('profile_update', [AuthController::class, 'updateProfile']);
        Route::post('change_password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

});
//provider route
Route::middleware('auth:api','provider')->group(function () {
    Route::post('service_category', [AuthController::class, 'createServiceCategory']);
    Route::post('update_service_category/{id}', [AuthController::class, 'updateServiceCategory']);
    Route::get('service_category_list', [AuthController::class, 'ServiceCategoryList']);
    Route::get('service_category_details/{id}', [AuthController::class, 'ServiceCategoryDetails']);
});
