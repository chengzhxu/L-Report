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
    Route::any('report/get_region_pv', 'ReportController@getRegionPv');
    Route::any('report/get_region_uv', 'ReportController@getRegionUv');
    Route::any('report/get_realtime_data', 'ReportController@getRealTimeData');
    Route::any('report/get_region_pv_by_day', 'ReportController@getRegionPvByDay');
    Route::any('report/get_uv_by_day', 'ReportController@getUvByDay');
    Route::any('report/get_history_pv', 'ReportController@getHistoryPv');
    Route::any('report/get_history_uv', 'ReportController@getHistoryUv');

    Route::any('report/get_region_list', 'ReportController@getRegionList');

    Route::any('report/test', 'ReportController@test');
    Route::any('report/get_cpanel_group', 'ReportController@getCpanelGroup');
    Route::post('user/logout', 'UserController@logout');
});


Route::group(['prefix' => '','namespace' => 'Report'], function () {
    Route::post('user/login', 'UserController@login');
});
