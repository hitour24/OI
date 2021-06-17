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

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');

Route::get('/v1/removeCampaign/{campaignId}','Api\NetworkController@removeCampaign');
Route::post('/v1/addCampaign/','Api\NetworkController@addCampaign');
Route::post('/v1/addCampaigns/','Api\NetworkController@addCampaigns');
Route::get('/v1/getBalance/{network}','Api\NetworkController@getBalance');
Route::get('/v1/getMinBid/sourceId={sourceId}&countryCode={countryCode}','Api\NetworkController@getMinBid');
Route::put('/v1/setPlacements/','Api\NetworkController@setPlacements');
