<?php

Route::post('/login', 'LoginController@login');
Route::post('/login/refresh', 'LoginController@refresh');
Route::post('/register', 'RegisterController@register');
Route::get('/verify-email/{token}', 'RegisterController@verify')->name('auth.verify_email');
