<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Provider\ServiceCategoryController;
use App\Http\Controllers\Provider\ServiceController;
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
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::middleware('auth:api')->group(function () {
        Route::get('own-profile', [AuthController::class, 'ownProfile']);
        Route::post('profile-update', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

});
//provider route
Route::middleware(['auth:api', 'provider'])->group(function () {

    //add category
    Route::get('get-all-category', [ServiceCategoryController::class, 'getCategory']);
    Route::get('get-all-subcategory', [ServiceCategoryController::class, 'getSubCategory']);
    Route::post('create-with-subcategory', [ServiceCategoryController::class, 'storeCategoryWithSubcategory']);
    Route::post('update-with-subcategory/{id}', [ServiceCategoryController::class, 'UpdateCategoryWithSubcategory']);
    Route::post('update-subcategory/{id}', [ServiceCategoryController::class, 'updateSubcategory']);
    Route::delete('delete-subcategory/{id}', [ServiceCategoryController::class, 'deleteSubcategory']);
    Route::delete('delete-category/{id}', [ServiceCategoryController::class, 'deleteServiceCategory']);

    //route for service
    Route::post('create-service', [ServiceController::class, 'createServices']);
    Route::post('update-service/{id}', [ServiceController::class, 'updateServices']);
    Route::delete('delete-service/{id}', [ServiceController::class, 'deleteSubcategory']);
    Route::get('get-all-services', [ServiceController::class, 'getServices']);
    Route::get('get-services-details/{id}', [ServiceController::class, 'servicesDetails']);

});
