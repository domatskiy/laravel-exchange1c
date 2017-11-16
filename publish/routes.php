<?php

$path = config('1c_ut_exchange.exchange_path', '1c_exchange');

Route::group(['middleware' => [\Illuminate\Session\Middleware\StartSession::class]], function () use ($path) {
    Route::match(['get', 'post'], $path, '\Domatskiy\Exchange1C\Controller\ImportController@request');
});