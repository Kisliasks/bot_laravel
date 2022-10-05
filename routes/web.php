<?php

use App\Http\Controllers\WebhookController;
use App\Http\Controllers\DataController;
use App\Modelsl\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

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




Route::group(['namespace' => 'App\Http\Controllers'], function () {

    Route::post('/webhook', function() {

        return response()->json(true, 200);
        
       
    });
});

$appPath = 'App\Http\Controllers';
Route::get('/d', $appPath.'\DataController@getInfo');
// Route::post('/webhook', $appPath.'\WebhookController@index');