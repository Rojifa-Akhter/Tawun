<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Provider\ServiceCategoryController;
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
    Route::post('create-with-subcategory', [ServiceCategoryController::class, 'storeCategoryWithSubcategory']);
    Route::post('create-subcategory', [ServiceCategoryController::class, 'storeSubcategory']);
    Route::post('update-subcategory/{id}', [ServiceCategoryController::class, 'updateSubcategory']);
    Route::delete('delete-subcategory/{id}', [ServiceCategoryController::class, 'deleteSubcategory']);
    Route::delete('delete-category/{id}', [ServiceCategoryController::class, 'deleteServiceCategory']);

});
