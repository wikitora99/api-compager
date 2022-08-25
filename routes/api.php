<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    CompanyController,
    EmployeeController,
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('login', fn () => response()->json(['message' => 'Unauthenticated'], 403) )->name('login');
        Route::post('login', 'login')->name('login.post');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user() );
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::resource('company', CompanyController::class)->except(['create', 'edit']);
    });
});
