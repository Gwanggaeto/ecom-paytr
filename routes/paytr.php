<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PayTR Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web', 'theme', 'locale', 'currency']], function () {

    // PayTR Payment Routes
    Route::prefix('paytr')->name('paytr.')->group(function () {

        // Ödeme sayfasına yönlendirme
        Route::get('/redirect', 'PayTRController@redirect')->name('redirect');

        // PayTR callback (webhook) - POST olmalı
        Route::post('/callback', 'PayTRController@callback')->name('callback');

        // Başarılı ödeme dönüşü
        Route::get('/success', 'PayTRController@success')->name('success');

        // İptal edilen ödeme dönüşü
        Route::get('/cancel', 'PayTRController@cancel')->name('cancel');

        // AJAX sipariş durum kontrolü
        Route::post('/check-status', 'PayTRController@checkOrderStatus')->name('check-status');
    });

});