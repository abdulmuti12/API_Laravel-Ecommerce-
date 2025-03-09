<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\OrderCustomerController;
use App\Http\Controllers\Customer\ProductCustomerController;
use App\Http\Controllers\Customer\TransactionCustomerController;
use App\Http\Controllers\Customer\CustomerController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([ 'prefix' => 'admin'], function () {
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::apiResource('products', ProductController::class);
        Route::get('transactionConfirmation/{id}', [TransactionController::class, 'transactionConfirmation']);
        Route::get('transactionDetail/{id}', [TransactionController::class, 'transactionDetail']);
        Route::get('transactionList', [TransactionController::class, 'transactionList']);
        Route::apiResource('emailSubscribe', EmailController::class);
        Route::get('sendMail', [EmailController::class, 'sendMail']);


    });

});

Route::post('register', [CustomerAuthController::class, 'register']);
Route::post('login', [CustomerAuthController::class, 'login']);
Route::get('/customer/login', [CustomerAuthController::class, 'login'])->name('login');
Route::apiResource('products', ProductCustomerController::class);

Route::middleware(['auth:customer'])->group(function () {
    Route::post('logout', [CustomerAuthController::class, 'logout']);
    Route::post('ordersProceess', [OrderCustomerController::class, 'proccessOrder']);
    Route::apiResource('orders', OrderCustomerController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::post('checkoutDetail', [OrderCustomerController::class, 'checkoutDetail']);
    Route::get('countBracket', [OrderCustomerController::class, 'countBracket']);
    Route::get('updateSubscribe/{id}', [CustomerController::class, 'updateSubscribe']);

    Route::post('updateCheckout', [OrderCustomerController::class, 'updateCheckout']);
    Route::post( 'deleteCheckout', [OrderCustomerController::class, 'deleteCheckout']);
    Route::post( 'transactionProcess', [TransactionCustomerController::class, 'transactionProcess']);
});

