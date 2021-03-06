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

Route::post('create_call','CallController@createCall');
Route::get('utc_current_time',function () {
    $dateTime =['time'=>Carbon\Carbon::now()->timezone('0')];
    return response($dateTime,200)
        ->header('Content-Type', 'application/json');
});
Route::post('lat_long','CallController@latLong');
Route::post('login','CallController@login');
Route::post('add_user','CallController@saveRecord');
