<?php

namespace App\Http\Controllers;


use App\Helpers\Telegram;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
   
    

    public function getInfo() {
      $data = Users::find(1);
      dd($data); 
    }

    public function newUserInGroup() {

      Users::create([
        'telegram_id' => '4389992834', 
        'username' => 'testusername'
     ]);

    }

    public function updateNewIser() {

      $user = Users::where('telegram_id','4389992834')->where('username', 'testusername');
      $user->update([
        'fullname' => 'Антон Голичев',
        'date_of_birth' => '1993-09-26',
        'office_number' => '403',

      ]);

    }

    public function date() {
      date_default_timezone_set('Europe/Moscow');
      $time_h = Date('H');
      $time_m = Date('i');
      if($time_h >= '13' AND $time_m >= '21') {
        echo 'Время истекло!';
        
      }
    }

    

    // id | telegram_id | full_name | date_of_birth | office_number


}
