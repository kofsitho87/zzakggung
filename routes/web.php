<?php

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

Auth::routes();

Route::group(['middleware' => 'auth'], function(){
    Route::get('/', 'HomeController@index');
    Route::get('/user/change_pw', 'HomeController@changePwView');
    Route::post('/user/change_pw', 'HomeController@changePw')->name('home.changePw');

    Route::prefix('orders')->group(function(){
        //샘플파일다운로드
        Route::get('/example', 'OrderController@productsExample')->name('order.example');

        //거래내역서
        Route::get('/trade', 'OrderController@tradeView')->name('order.trade');

        //주문내역 업데이트
        Route::put('/{order}', 'OrderController@update')->name('order.update')->where('order', '[0-9]+');

        //주문내역다운로드
        Route::get('/export', 'OrderController@export')->name('order.export');

        //주문내역리스트
        Route::get('/list', 'OrderController@listView');

        //다량주문업로드 뷰
        Route::get('/upload', 'OrderController@uploadView');

        //다량주문업로드 엑셀임시업로드
        Route::post('/upload/excel', 'OrderController@uploadExcel')->name('order.uploadExcel');
        //다량주문업로드 최종디비에업로드
        Route::post('/upload', 'OrderController@uploadOrders')->name('order.uploadOrders');

        //거래내역확인
        Route::get('/user/trades', 'UserController@tradesView')->name('user.trades');
    }); 

    Route::prefix('admin')->group(function(){
        Route::get('/', 'AdminController@index')->name('admin.index');

        Route::get('/shop_types', 'AdminController@shopTypes')->name('admin.shop_types');
        Route::put('/shop_types', 'AdminController@updateShopTypes');
        Route::post('/shop_types', 'AdminController@createShopTypes');
        Route::delete('/shop_types/{shop}', 'AdminController@deleteShopType')->where('shop', '[0-9]+');

        Route::get('/users', 'AdminController@users')->name('admin.users');
        Route::get('/users/{user}', 'AdminController@user')->where('user', '[0-9]+');
        Route::delete('/users/{user}', 'AdminController@deleteUser')->where('user', '[0-9]+');
        Route::get('/users/create', 'AdminController@createUserView')->name('admin.createUser');
        Route::post('/users', 'AdminController@createUser');
        Route::put('/users/{user}', 'AdminController@updateUser')->where('user', '[0-9]+');
        Route::get('/users/{user}/trades', 'AdminController@userTrades')->where('user', '[0-9]+');
        Route::post('/users/{user}/trades', 'AdminController@createUserTrades')->where('user', '[0-9]+');
        Route::delete('/users/{user}/trades', 'AdminController@deleteUserTrades')->where('user', '[0-9]+');

        //주문내역
        Route::get('/orders', 'AdminController@orders')->name('admin.orders');
        Route::put('/orders', 'AdminController@updateOrders');

        Route::get('/orders/{order}', 'AdminController@order')->name('admin.order')->where('order', '[0-9]+');
        Route::put('/orders/{order}', 'AdminController@updateOrder')->where('order', '[0-9]+');
        Route::put('/orders/{order}/receiver', 'AdminController@updateOrderReceiver')->where('order', '[0-9]+');
        Route::put('/orders/{order}/status/{status}', 'AdminController@updateOrderStatus')->where('order', '[0-9]+')->where('status', '[0-9]+');
        Route::delete('/orders/{order}', 'AdminController@deleteOrder')->where('order', '[0-9]+');
        Route::get('/orders/export', 'AdminController@orderExport')->name('admin.orders.export');
        Route::post('/orders/upload', 'AdminController@orderImport')->name('admin.orders.import');
        
        Route::get('/products', 'AdminController@products')->name('admin.products');
        Route::get('/products/{product}', 'AdminController@product')->where('product', '[0-9]+');
        Route::put('/products/{product}', 'AdminController@updateProduct')->where('product', '[0-9]+');
        Route::delete('/products/{product}', 'AdminController@deleteProduct')->where('product', '[0-9]+');
        Route::get('/products/create', 'AdminController@createProductView')->name('admin.products.create');
        Route::post('/products', 'AdminController@createProduct');
        Route::post('/products/upload', 'AdminController@createProductByExcel');
        Route::get('/products/example', 'AdminController@productsExample');

        Route::get('/delivery/providers', 'AdminController@deliveryProviders');
        Route::post('/delivery/providers', 'AdminController@createDeliveryProvider');

        //공지사항
        Route::get('/notice', 'NoticeController@createView');
        Route::post('/notice', 'NoticeController@create');
    });
});
