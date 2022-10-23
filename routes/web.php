<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers as Controllers;
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

Route::get('/', function () {
    return view('welcome');
});

/* На данный роут отправляем запрос */
Route::post('/payment', [Controllers\PaymentPageController::class, 'update'])->name('payment.update');

Route::get('/payment', [Controllers\PaymentPageController::class, 'update'])->name('payment.update');