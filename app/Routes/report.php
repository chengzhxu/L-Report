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


use Illuminate\Routing\Router;

//Route::group(array('prefix' => '', 'namespace' => 'Report', 'middleware' => ['admin']), function(){
//    Route::get('report/test', 'ReportController@test');
//    // 资源路由
//});

Route::group(['prefix' => '','namespace' => 'Report', 'middleware' => ['auth.report'],], function () {
    Route::any('report/test', 'ReportController@test');
    Route::post('user/logout', 'UserController@logout');
});


Route::group(['prefix' => '','namespace' => 'Report',], function () {
    Route::post('user/login', 'UserController@login');
});
