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

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'admin'], function(){
    //config
    Route::get('/config/shopTypes', 'Api\AdminController@shopTypes');
    Route::get('/config/deliveryProviders', 'Api\AdminController@deliveryProviders');
    
    //get users
    Route::get('/users', 'Api\AdminController@users');
    //get user
    Route::get('/user/{user}', 'Api\AdminController@user')->where('user', '[0-9]+');
    //update user
    Route::put('/user/{user}', 'Api\AdminController@updateUser')->where('user', '[0-9]+');

    //get user trades
    Route::get('/users/{user}/trades', 'Api\AdminController@userTrades')->where('user', '[0-9]+');
    Route::post('/users/{user}/trades', 'Api\AdminController@addUserTrade')->where('user', '[0-9]+');
    Route::delete('/users/{user}/trades', 'Api\AdminController@deleteUserTrades')->where('user', '[0-9]+');

    //get orders
    Route::get('/orders', 'Api\AdminController@orders');
    //get order
    Route::get('/orders/{order}', 'Api\AdminController@order')->where('order', '[0-9]+');
    //update orders
    Route::put('/orders', 'Api\AdminController@updateOrders');
    //update order
    Route::put('/orders/{order}', 'Api\AdminController@updateOrder')->where('order', '[0-9]+');
    //delete orders
    Route::delete('/orders', 'Api\AdminController@deleteOrders');

    //get products
    Route::get('/products', 'Api\AdminController@products');
    //get product
    Route::get('/products/{product}', 'Api\AdminController@product')->where('order', '[0-9]+');
    
    //update order receiver
    Route::put('/orders/{order}/receiver', 'Api\AdminController@updateOrderReceiver')->where('order', '[0-9]+');
});

Route::post('/auth/login', 'AuthController@login');
Route::get('/notice', 'NoticeController@get');
