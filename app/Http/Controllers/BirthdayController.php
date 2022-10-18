<?php

namespace App\Http\Controllers;


use App\Helpers\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use  App\Http\Controllers\DataController;
use App\Models\Users;

use Illuminate\Support\Facades\Http;


class BirthdayController extends Controller
{
   

   public function __invoke(Request $request, Telegram $telegram) {


   
    $date_day = date('d', strtotime('+3 day'));
    $date_month = date('m', strtotime('+3 day'));
   
    
    $user = Users::select('username', 'date_of_birth', 'fullname', 'office_number')->whereMonth('date_of_birth', $date_month)->whereDay('date_of_birth', $date_day)->get();
  foreach($user as $val) {
    $result_date = $val->date_of_birth;
    $result_user = $val->username;
    $result_fullname = $val->fullname;
    $result_office_number = $val->office_number;
  }
   
     if(!empty($result_date)) {

      $other_users = Users::select('username', 'telegram_id')->whereMonth('date_of_birth', '!=', $date_month)->whereDay('date_of_birth', '!=', $date_day)->get();
      foreach($other_users as $other_user) {
        $tg_id = $other_user->telegram_id;
        echo $tg_id;
        $message = 'У пользователя '.$result_user.' '.$result_fullname.' '.$result_date.' '.$result_office_number.' скоро день рождения! Давай поздравим его.';
        $telegram->sendMessage($tg_id,$message);
      }
     }
   }

  
  }