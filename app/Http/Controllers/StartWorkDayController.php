<?php

namespace App\Http\Controllers;

use App\Helpers\Bothelper;
use App\Helpers\Telegram;
use App\Models\MessageId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Date;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Http\Client\Response; 


class StartWorkDayController extends Controller
{
   

   public function buttons() {


    $message = 'Вы готовы приступить к работе?';
   
    
    $users = Users::select('telegram_id','username', 'date_of_birth', 'fullname', 'office_number')->get();
    foreach($users as $user) {
      $telegram_id = $user->telegram_id;
      
      $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$telegram_id)->get();
      foreach($db_chat_id as $db) {
        
        // $response = Telegraph::chat($db->id)->message($message)
        // ->keyboard(Keyboard::make()->buttons([
        //         Button::make('Да')->action('Да'),
        //         Button::make('Нет')->action('Нет'),
                
        // ]))->send();
        
       
        // $message_id = $response->telegraphMessageId();
        
        // json_decode($message_id);
        // $chat_id = TelegraphChat::find(1);
        // echo $chat_id->chat_id;
        // MessageId::insert([
        //   'message_id' => $message_id,
        //   'type' => 'start_work',
        //   'chat_id' => $chat_id->chat_id
        // ]);
       
      }
    }
    // тестовый 
    
    
    $response = Telegraph::chat(TelegraphChat::find(1))->message($message)
    ->keyboard(Keyboard::make()->buttons([
            Button::make('Да')->action('Да'),
            Button::make('Нет')->action('Нет'),
            
    ]))->send();
    
   
    $message_id = $response->telegraphMessageId();
    
    json_decode($message_id);
    $chat_id = TelegraphChat::find(1);
    echo $chat_id->chat_id;
    MessageId::insert([
      'message_id' => $message_id,
      'type' => 'start_work',
      'chat_id' => $chat_id->chat_id
    ]);
     
    
   }
  
   public function __invoke(Bothelper $bothelper) {
      // сообщения должны приходить администратору а не в тестовую группу!
   
   $users_not_work = $bothelper->selectNotWorkUsers();
   $users_work = $bothelper->selectWorkUsers();
   date_default_timezone_set('Europe/Moscow');
    $date = Date("d.m.Y H:i");
    if(!empty($users_not_work) && !empty($users_work)) {

      $message = 'Статистика на сегодня '.'<i>'.$date.'</i>'.PHP_EOL.PHP_EOL.
      'К работе приступили:'.PHP_EOL.$users_work.PHP_EOL.
      'Не приступили к работе'.PHP_EOL.$users_not_work;

      $chat = TelegraphChat::find(1);
      $chat->message($message)->send();  
   
    }
    if(empty($users_not_work) && !empty($users_work)) {

      $message = 'Статистика на сегодня '.'<i>'.$date.'</i>'.PHP_EOL.PHP_EOL.
      'К работе приступили:'.PHP_EOL.$users_work;
      $chat = TelegraphChat::find(1);
      $chat->message($message)->send();  
    }
    if(empty($users_work) && !empty($users_not_work)) {

      $message = 'Статистика на сегодня '.'<i>'.$date.'</i>'.PHP_EOL.PHP_EOL.
      'Не приступили к работе:'.PHP_EOL.$users_not_work;
      $chat = TelegraphChat::find(1);
      $chat->message($message)->send();  
    }
    if(empty($users_work) && empty($users_not_work)) {

      $message = 'Статистики нет.';
      $chat = TelegraphChat::find(1);
      $chat->message($message)->send();  
      
    }
   
   }

   public function unsetWorkStatus() {

    Users::where('work_status', 'Да')->orWhere('work_status', 'Нет')->update([
    'work_status' => ''
   ]);;
  
   }

   public function startWorkTimeOut() {

   $message_id = MessageId::all();
    foreach($message_id as $val) {
   
     $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$val->chat_id)->get();
      foreach($db_chat_id as $db) {
       
        $chat = TelegraphChat::find($db->id);
        $chat->edit($val->message_id)->message("Вы сделали свой выбор. Хорошего дня!")->send();
      }
     $message = MessageId::where('type', 'start_work');
      $message->delete();
    }

    $addNotWorkStatus = Users::where('work_status', null)->orWhere('work_status', '');
   
    $addNotWorkStatus->update([
      'work_status' => 'Нет'
    ]);
  


   }

  
  }