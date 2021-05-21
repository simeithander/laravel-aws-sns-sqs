<?php

use App\Http\Controllers\{
    PostController,
    HomeController
};
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// SQS
Route::get('/sqs-notifications', [PostController::class, 'getSQSNotifications']);
// SNS
Route::get('/sns-list-subscriptions', [PostController::class, 'listSNSSubscriptions']);
Route::get('/sns-send-notification', [PostController::class, 'sendSNSNotification']);

// Form Mail
Route::post('/send-mail', [PostController::class, 'sendMail']);

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
