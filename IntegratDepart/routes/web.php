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

//Route::get('/api/v1/removeCampaign/sourceId={sourceId}&campaignId={campaignId}','NetworkController@removeCampaign');
//Route::post('/api/v1/addCampaign/','NetworkController@addCampaign');
//Route::get('/api/v1/getBalance/{network}','NetworkController@getBalance');


Route::get('/test/', function () {

    require_once '../app/classes/Network.php';
    require_once '../app/classes/Networks/Clickadu.php';
    require_once '../app/classes/UserNetwork.php';
//    $user = new \App\Classes\Network\Network(new \App\Classes\Network\UserNetwork());
//    return $user->getBalance();

    $user = new \App\Classes\Network\Clickadu(new \App\Classes\Network\UserNetwork());
    return dd($user->getBalance());

});
