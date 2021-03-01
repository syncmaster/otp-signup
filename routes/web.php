<?php

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

Route::get('/', 'AuthController@index');
Route::post('/register', 'AuthController@register')->name('auth.register');
Route::post('/code', 'AuthController@sendNewCode')->name('auth.generate.code');
Route::POST('/verify', 'AuthController@verifyPhoneNumber')->name('auth.verify.number');
Route::post('/attempts', 'AuthController@setAttemptsToZero')->name('set.attempts.zero');
