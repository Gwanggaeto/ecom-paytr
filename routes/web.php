<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// PayTR Payment Routes
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency']], function () {
    Route::prefix('paytr')->name('paytr.')->group(function () {
        Route::get('/redirect', 'Webkul\Shop\Http\Controllers\PayTRController@redirect')->name('redirect');
        Route::post('/callback', 'Webkul\Shop\Http\Controllers\PayTRController@callback')->name('callback');
        Route::get('/success', 'Webkul\Shop\Http\Controllers\PayTRController@success')->name('success');
        Route::get('/cancel', 'Webkul\Shop\Http\Controllers\PayTRController@cancel')->name('cancel');
        Route::post('/check-status', 'Webkul\Shop\Http\Controllers\PayTRController@checkOrderStatus')->name('check-status');
    });
});