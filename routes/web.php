<?php

use App\Http\Controllers\WebhookController;
use App\Http\Controllers\FullUserDataController;
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



$appPath = 'App\Http\Controllers';


Route::post('/webhook', $appPath.'\WebhookController');

Route::get('/data', $appPath.'\DataController@sendMessage');
Route::get('/work', $appPath.'\StartWorkDayController@buttons');
Route::get('/stat', $appPath.'\StartWorkDayController@statisticsWorkUsers');
Route::get('/timeout', $appPath.'\StartWorkDayController@startWorkTimeOut');
Route::get('/unset', $appPath.'\StartWorkDayController@unsetWorkStatus');








//https://api.telegram.org/bot5680287506:AAHL0zd4_ZzpfO7Frn8gnHem4kdOQWgBzi8/setWebhook?url=https://8ea7-94-29-16-107.eu.ngrok.io/webhook