<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'admin'], function(){
    Route::get('/config/shopTypes', 'Api\AdminController@shopTypes');

    Route::get('/users', 'Api\AdminController@users');
    Route::get('/user/{user}', 'Api\AdminController@user')->where('user', '[0-9]+');
    Route::get('/user/{user}/trades', 'Api\AdminController@userTrades')->where('user', '[0-9]+');

    Route::get('/orders', 'Api\AdminController@orders');
});

Route::get('/notice', 'NoticeController@get');
