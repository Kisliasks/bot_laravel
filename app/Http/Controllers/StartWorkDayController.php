<?php

namespace App\Http\Controllers;

use App\Helpers\Bothelper;
use App\Helpers\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use  App\Http\Controllers\DataController;
use App\Models\Users;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;


class StartWorkDayController extends Controller
{
   

   public function buttons(Request $request, Telegram $telegram) {


    $message = 'Вы готовы приступить к работе?';
    $buttons = [
      'inline_keyboard' => [
        [
          [
            'text' => 'Да',
            'callback_data' => 'Да'
          ],
          [
            'text' => 'Нет',
            'callback_data' => 'Нет'
          ]
        ]
      ]
    ];
    
    $users = Users::select('telegram_id','username', 'date_of_birth', 'fullname', 'office_number')->get();
    foreach($users as $user) {
      $telegram_id = $user->telegram_id;
      // $telegram->sendButtons('-1001813201867', $message, $buttons);   // использовать если много пользователей 
    }
      $response = $telegram->sendButtons('-1001813201867', $message, $buttons); // тестовый 

     dd($response);
     

    
   }
  
   public function statisticsWorkUsers(Telegram $telegram, Bothelper $bothelper) {
      
   
   $users_not_work = $bothelper->selectNotWorkUsers();
   $users_work = $bothelper->selectWorkUsers();
   date_default_timezone_set('Europe/Moscow');
    $date = Date("d.m.Y H:i");
    if(!empty($users_not_work) && !empty($users_work)) {

      $message = 'Статистика на сегодня '.'<i>'.$date.'</i>'.PHP_EOL.PHP_EOL.
      'К работе приступили:'.PHP_EOL.$users_work.PHP_EOL.
      'Не приступили к работе'.PHP_EOL.$users_not_work;

      $telegram->sendMessage('-1001813201867', $message);
   
    }
    if(empty($users_not_work) && !empty($users_work)) {

      $message = 'Статистика на сегодня '.'<i>'.$date.'</i>'.PHP_EOL.PHP_EOL.
      'К работе приступили:'.PHP_EOL.$users_work;
      $telegram->sendMessage('-1001813201867', $message);
    }
    if(empty($users_work) && !empty($users_not_work)) {

      $message = 'Статистика на сегодня '.'<i>'.$date.'</i>'.PHP_EOL.PHP_EOL.
      'Не приступили к работе:'.PHP_EOL.$users_not_work;
      $telegram->sendMessage('-1001813201867', $message);
    }
    if(empty($users_work) && empty($users_not_work)) {

      $message = 'Статистики нет.';
      $telegram->sendMessage('-1001813201867', $message);
      
    }
   
   }

   public function unsetWorkStatus() {

    Users::where('work_status', 'Да')->orWhere('work_status', 'Нет')->update([
    'work_status' => ''
   ]);;
  
   }

  
  }