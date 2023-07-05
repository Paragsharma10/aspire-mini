<?php

use App\Http\Controllers\api\LoansController;
use App\Http\Controllers\api\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/user-data', [UsersController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::prefix('loan')->group(function () {
        Route::post('/', [LoansController::class, 'store']);
        Route::post('/get-loan', [LoansController::class, 'index']);
        Route::post('/loan-repayment', [LoansController::class, 'loanRepayment']);
    });
    Route::prefix('loan/admin')->group(function () {
        Route::post('/change-status', [LoansController::class, 'ApproveLoan']);
    });
});
