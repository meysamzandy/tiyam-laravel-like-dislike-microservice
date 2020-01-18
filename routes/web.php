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

Route::prefix('_api/_v1/votes')->group(function () {
    Route::get('/', 'ReActionsController@getData');
    Route::post('/list', 'ReActionsController@getList');
    Route::post('/like', 'ReActionsController@setLike');
    Route::post('/dislike', 'ReActionsController@setDislike');
});
