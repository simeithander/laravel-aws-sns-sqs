<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    NotificationsController
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Notifications
Route::post('/notifications', [NotificationsController::class, 'store'])->name('store.notifications');
Route::put('/notifications', [NotificationsController::class, 'update'])->name('update.notifications');
Route::delete('/notifications', [NotificationsController::class, 'delete'])->name('delete.notifications');