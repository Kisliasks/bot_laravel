<?php

namespace App\Http\Controllers;


use App\Helpers\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function index(Request  $request){
        Log::debug($request->all());

      // $public = $request->input('message')['text'];
      // return dd(json_decode($public));
       
    }

}
