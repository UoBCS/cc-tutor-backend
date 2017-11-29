<?php

Route::post('/login', 'LoginController@login');
Route::post('/login/refresh', 'LoginController@refresh');
