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

Route::group(['middleware' => 'auth'], function () {
  Route::get('/', 'HomeController@index');
  Route::get('/user/change_pw', 'HomeController@changePwView');
  Route::post('/user/change_pw', 'HomeController@changePw')->name('home.changePw');
  Route::prefix('orders')->group(function () {
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

  Route::prefix('admin')->group(function () {
    Route::get('/products/example', 'AdminController@productsExample');
  });
});


Route::prefix('admin')->group(function () {
  Route::get('/orders/export', 'Api\AdminController@orderExport')->name('admin.orders.export');
  Route::get('/', 'SpaController@index')->where('any', '.*');
  Route::get('{any}', 'SpaController@index')->where('any', '.*');
});
