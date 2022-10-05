<?php

namespace App\Http\Controllers;


use App\Helpers\Telegram;
use App\Models\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
   
    public function getInfo() {
      $data = Data::find(1);
      dd($data); 
    }

}
