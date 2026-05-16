<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DispatchController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\VariantController;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;

Route::post('v1/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('v1/logout', [AuthController::class, 'logout']);
    Route::get('v1/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::apiResource('v1/products', ProductController::class);
    Route::apiResource('v1/variants', VariantController::class);
    Route::apiResource('v1/orders', OrderController::class);
    Route::post('v1/invoices/{invoice}/generate-pdf', [InvoiceController::class, 'generatePdf']);
    Route::apiResource('v1/invoices', InvoiceController::class);
    Route::apiResource('v1/payments', PaymentController::class);
    Route::post('v1/dispatches/{dispatch}/confirm', [DispatchController::class, 'confirm']);
    Route::apiResource('v1/dispatches', DispatchController::class);
    Route::apiResource('v1/customers', CustomerController::class);
});
