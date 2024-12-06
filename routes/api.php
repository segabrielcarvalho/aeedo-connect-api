<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\OrganController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'Up']);
});

Route::post('users', [UserController::class, 'registerUser']);

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::prefix('addresses')->group(function () {
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::post('', [AddressController::class, 'store']);
        Route::patch('/{id}', [AddressController::class, 'update']);
    });

    Route::prefix('hospitals')->group(function () {
        Route::get('/', [HospitalController::class, 'index']);
        Route::get('/{id}', [HospitalController::class, 'getHospitalInfo']);
        Route::post('choose-hospitals', [HospitalController::class, 'chooseHospitals']);
    });

    Route::prefix('organs')->group(function () {
        Route::get('patient-organs/{patientId?}', [OrganController::class, 'listPatientOrgans']);
        Route::post('choose-organs', [OrganController::class, 'chooseOrgans']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/list/{type?}', [UserController::class, 'listUsers']);
        Route::get('/type/{type?}', [UserController::class, 'listUsersByType']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::patch('/{id}', [UserController::class, 'update']);
    });
    

    Route::get('patient-details/{id}', [PatientController::class, 'details']);
});
