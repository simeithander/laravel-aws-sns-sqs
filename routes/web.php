<?php

use App\Http\Controllers\{
    PostController,
    HomeController,
    SendMailController,
    MobilePushController
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

// SES
Route::get('/list-mails', [SendMailController::class, 'listMails']);
Route::post('/post-mail', [SendMailController::class, 'postMailSES']);
Route::post('/add-emails', [SendMailController::class, 'addEmails']);

// SES Mobile
Route::get('/send-mobile', [MobilePushController::class, 'sendNotification']);
Route::get('/create-plataform-endpoint', [MobilePushController::class, 'createPlataformEndpoint']);

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
