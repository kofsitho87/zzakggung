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

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'admin'], function () {
    //auth
    Route::post('/auth/changePw', 'Api\AdminController@changePw');

    //history
    Route::get('/history', 'UpdateHistoryController@getItems');
    Route::post('/history', 'UpdateHistoryController@create');
    Route::put('/history/{history}', 'UpdateHistoryController@update')->where('history', '[0-9]+');
    Route::delete('/history/{history}', 'UpdateHistoryController@delete')->where('history', '[0-9]+');

    //config
    Route::get('/config/shopTypes', 'Api\AdminController@shopTypes');
    Route::get('/config/deliveryProviders', 'Api\AdminController@deliveryProviders');
    Route::post('/config/deliveryProviders', 'Api\AdminController@createDeliveryProviders');

    //get users
    Route::get('/users', 'Api\AdminController@users');
    //get user
    Route::get('/user/{user}', 'Api\AdminController@user')->where('user', '[0-9]+');
    //update user
    Route::put('/user/{user}', 'Api\AdminController@updateUser')->where('user', '[0-9]+');
    //create user
    Route::post('/user', 'Api\AdminController@createUser');
    //delete user
    Route::delete('/user/{user}', 'Api\AdminController@deleteUser')->where('user', '[0-9]+');

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
    Route::post('/orders', 'Api\AdminController@deleteOrders');
    //upload orders
    Route::post('/orders/upload', 'Api\AdminController@orderImport');

    //get products
    Route::get('/products', 'Api\AdminController@products');
    //get product
    Route::get('/products/{product}', 'Api\AdminController@product')->where('order', '[0-9]+');
    //update product
    Route::put('/products/{product}', 'Api\AdminController@updateProduct')->where('order', '[0-9]+');
    //delete product
    Route::delete('/products/{product}', 'Api\AdminController@deleteProduct')->where('order', '[0-9]+');
    //create product
    Route::post('/products', 'Api\AdminController@createProduct');

    //update order receiver
    Route::put('/orders/{order}/receiver', 'Api\AdminController@updateOrderReceiver')->where('order', '[0-9]+');

    //create shop type
    Route::post('/shop_types', 'Api\AdminController@createShopType');
    //update shop type
    Route::put('/shop_types/{shopType}', 'Api\AdminController@updateShopType')->where('shopType', '[0-9]+');
    //delete shop type
    Route::delete('/shop_types/{shopType}', 'Api\AdminController@deleteShopType')->where('shopType', '[0-9]+');

    //create notice
    Route::post('/notices', 'Api\AdminController@createNotice');
    //get notices
    Route::get('/notices', 'Api\AdminController@notices');
    //get notice
    Route::get('/notices/{notice}', 'Api\AdminController@notice')->where('notice', '[0-9]+');
    //update notices
    Route::put('/notices/{notice}', 'Api\AdminController@updateNotice')->where('notice', '[0-9]+');
    //delete notices
    Route::delete('/notices/{notice}', 'Api\AdminController@deleteNotice')->where('notice', '[0-9]+');
    //post image
    Route::post('/notices/upload/image', 'Api\AdminController@uploadNoticeImage');


    //get db list
    Route::get('/db/raw_orders', 'Api\AdminController@rawOrders');
    Route::post('/db/delete_all', 'Api\AdminController@deleteAllOrders');


    Route::post('/products/upload', 'Api\AdminController@createProductByExcel');
});

Route::post('/auth/login', 'AuthController@login');
Route::get('/notice', 'NoticeController@get');
