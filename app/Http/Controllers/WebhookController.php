<?php

namespace App\Http\Controllers;

use App\Helpers\Bothelper;
use App\Helpers\Telegram;
use App\Models\Birthday;
use App\Models\MessageId;
use DefStudio\Telegraph\Models\TelegraphChat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class WebhookController extends Controller
{
   
  



    public function __invoke(Request $request, Telegram $telegram, Bothelper $bothelper) {

      $group = TelegraphChat::find(1);
      $test_group_id = $group->chat_id;
     

      $username = $request->input('message');
      $callback_query = $request->input('callback_query');
      
    

      // готовность к рабочему дню /////////////////////////////////////////
      
      if(!empty($callback_query)) {
        $full_data_buttons = $callback_query;
         $message_id = $full_data_buttons['message']['message_id'];
         $data = $full_data_buttons['data'];
         $username = '@'.$full_data_buttons['from']['username'];
         $chat_id = $full_data_buttons['message']['chat']['id'];
        $user_from_id = $full_data_buttons['from']['id'];

         
      
                           
        switch($full_data_buttons) {
                        
              case $data == 'action:Да' :
               
                  $message = 'Отлично! Вы готовы работать.';
                
                  $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$chat_id)->get();
                  
                    foreach($db_chat_id as $db) {
                    
                      $chat = TelegraphChat::find($db->id);
                      $chat->edit($message_id)->message($message)->send();
                    }
                  
                  $message = MessageId::where('chat_id', $user_from_id);   // при массовой применить $chat_id. при таестовой $user_from_id  
                    $message->delete();



                  $bothelper->insertWorkStatus('Да', $username);
                  
                unset($full_data_buttons);

              break;

              case !empty($data_bad_resp) || $data == 'action:Нет' :
                $message = 'Очень жаль, что вы не готовы начать рабочий день.';
                $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$chat_id)->get();
                    foreach($db_chat_id as $db) {
                    
                      $chat = TelegraphChat::find($db->id);
                      $chat->edit($message_id)->message($message)->send();
                    }
                  $message = MessageId::where('chat_id', $chat_id);
                    $message->delete();



                  $bothelper->insertWorkStatus('Нет', $username);

                unset($full_data_buttons);
                break;
          }
        }
//////////////////////////////Ответ по дню рождения //////////////

if(!empty($callback_query)) {
  $full_data_buttons = $callback_query;
   $message_id = $full_data_buttons['message']['message_id'];
   $data = $full_data_buttons['data'];
   $username = '@'.$full_data_buttons['from']['username'];
   $chat_id = $full_data_buttons['message']['chat']['id'];
   $user_from_id = $full_data_buttons['from']['id'];
                     
  switch($full_data_buttons) {
                  
        case str_contains($data,'action:Да.др'):
        $birth_user_id  = trim(strrchr($data, ":"), ':');
         
           
            $users = Users::where('telegram_id', $birth_user_id)->get();
          foreach($users as $usr) {
            $message =  $message = 'Отлично! Вы перевели средства на подарок '.$usr->username.PHP_EOL .'На всякий случай, ссылка все еще внизу ⬇️';
          }
            $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$chat_id)->get();
              foreach($db_chat_id as $db) {
                $payment_url = 'https://www.tinkoff.ru/cf/2JL5Kn4vRFj';
                $chat = TelegraphChat::find($db->id);
                $chat->edit($message_id)->message($message)->keyboard(Keyboard::make()->buttons([
                  Button::make('Ссылка на оплату')->url($payment_url)]))->send();
              }
              // удаляем сообщение из базы с учетом чата, из которого поступил ответ
            $message = MessageId::where('chat_id', $chat_id);
              $message->delete();
                // запись статуса ответа в таблицу birthday

              $result = Birthday::where([
                ['birth_user_id', $birth_user_id],
                ['another_user_id', $user_from_id],
            ]);
            if(!empty($result)) {
            $result->update([
              'status' => 'Оплатил'

            ]);

          }
            
          
          unset($full_data_buttons);

        break;

        case str_contains($data,'action:Нет.др') :
          $birth_user_id  = trim(strrchr($data, ":"), ':');
          $users = Users::where('telegram_id', $birth_user_id)->get();
          foreach($users as $usr) {
            $message = 'Вы отказались делать перевод средств на подарок '.$usr->username;
          }
         
          $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$chat_id)->get();
              foreach($db_chat_id as $db) {
              
                $chat = TelegraphChat::find($db->id);
                $chat->edit($message_id)->message($message)->send();
              }
            $message = MessageId::where('chat_id', $chat_id);
              $message->delete();

                // запись статуса ответа в таблицу birthday
                $result = Birthday::where([
                  ['birth_user_id', $birth_user_id],
                  ['another_user_id', $user_from_id],
              ]);
              if(!empty($result)) {
              $result->update([
                'status' => 'Отказался'
  
              ]);
  
            }

          

          unset($full_data_buttons);
          break;
    }
  }

       

      ////////////////////////////////////////////////////////////
      
      
      if(!empty($username)) {
      
        switch ($username) {
          case isset($username['new_chat_member']):
            $new_member = $username;
            Users::updateOrcreate(
              ['telegram_id' => $new_member['new_chat_member']['id']], 
              ['username' => "@".$new_member['new_chat_member']['username']]
           );
           TelegraphChat::updateOrcreate(
            ['chat_id' => $new_member['new_chat_member']['id']],
            ['name' => "@".$new_member['new_chat_member']['username']]
           );
          
           $message = "В группу вступил пользователь " .'@'.$new_member['new_chat_member']['username'];
           $chat = TelegraphChat::find(1);
           $chat->message($message)->send();
           
            unset($new_member);
            Log::debug($message);
            break;
          case isset($username['left_chat_member']) :
            $left_member = $username;
            $group_user_name = '@'.$left_member['left_chat_member']['username'];
            
            $user = Users::where('username', $group_user_name);
            $chat = TelegraphChat::where('name', $group_user_name);
            
            if(isset($user) && isset($chat)) {
              $user->delete();
              $chat->delete();

          $message = 'Пользователь '. $group_user_name. " вышел из группы";
          $chat = TelegraphChat::find(1);
          $chat->message($message)->send();
          unset($left_member);
          Log::debug($message);
        }
            break;
        }
      
      }
// обработка команд ===================================================

     if(isset($username['entities']) && $username['chat']['id'] == $test_group_id) {
        
        $full_data = $username;
        $text = $full_data['text'];
        $type = $full_data['entities'][0]['type'];
       
        switch ($full_data) {
          // работа с командой /new_admin

          case ($type === 'bot_command' && str_contains($text,'/new_admin')) :
            $textArr = explode(PHP_EOL, $text);
            
            if(count($textArr) < 2 || count($textArr) > 2 || $textArr[1] == '') {
              $message = $bothelper->bugMessageNewAdmin();
              $chat = TelegraphChat::find(1);
              $chat->message($message)->send();
              unset($full_data);
            } else {

              
            // результат, прошедший проверку 
            $username_tg = $textArr[1];
            $result = $bothelper->newAdminStatus($username_tg);

            if($result == true) {

             $message = 'Пользователь '. $username_tg." получил права администратора.";
             $chat = TelegraphChat::find(1);
             $chat->message($message)->send();
             Log::debug($message);
             unset($full_data); 
            }     
            if($result == false) {                          
             $message = $bothelper->unknownUserForNewAdmin($username_tg);
             $chat = TelegraphChat::find(1);
             $chat->message($message)->send();
             unset($full_data); 
            }
       
          }


            break;
          // работа с командой /info

          case ($type === 'bot_command' && str_contains($text,'/info')) :
            
            $info_bot = $bothelper->info_bot();
            $chat = TelegraphChat::find(1);
            $chat->message($info_bot)->send();
            Log::debug('Получение всей информации о работе бота');
            break;

          // работа с командой /all
          case ($type === 'bot_command' && str_contains($text,'/all')) :
            $usr = $bothelper->selectAllUsers();

            if(empty($usr)) {
              $message = $bothelper->notUsersAllComand();
              $chat = TelegraphChat::find(1);
              $chat->message($message)->send();
              unset($full_data);
              Log::debug('В группе пока нет пользователей');
             }
                if(!empty($usr)) {
                  $message = '<b>Пользователи:</b> '.PHP_EOL. $usr;
                  $chat = TelegraphChat::find(1);
                  $chat->message($message)->send();
                unset($full_data);
                Log::debug('Произошел вывод всех пользователей из базы');
                }
        
            break;
          
// команда edit ///////////////////////
            case ($type === 'bot_command' && str_contains($text,'/edit')) :
              $textArr = explode(PHP_EOL, $text);
             
// проверка на количество элементов в массиве
              if(count($textArr) < 5 || count($textArr) > 5 || $textArr[1] == '' || $textArr[2] == ''|| $textArr[3] == ''|| $textArr[4] == '') {
                $message = $bothelper->bagMessageFormat();
                $chat = TelegraphChat::find(1);
                $chat->message($message)->send();
                unset($full_data);
              } else {              
                $fullname =  $textArr[2];
                $date_of_birth = $textArr[3];
                $office_number = $textArr[4];
                $username_tg = $textArr[1];
  // `проверка на соответствие элементов массива              
                if(!preg_match('/(\d+)/s', $date_of_birth) || !str_contains($date_of_birth,'.')) {
                  $message = $bothelper->bagMessageFormat();
                  $chat = TelegraphChat::find(1);
                  $chat->message($message)->send();
                  unset($full_data);
// проверка на буквы в номере офиса
                } elseif(!is_numeric($office_number)) {
                  $message = $bothelper->bagMessageOfficeNumberFormat();
                  $chat = TelegraphChat::find(1);
                  $chat->message($message)->send();
                  unset($full_data);
                } else {
              
// работа с датой 
                        $correct_date = explode("-", str_replace(".", "-", $date_of_birth));
                        if(!is_numeric($correct_date[0]) || !is_numeric($correct_date[1]) || !is_numeric($correct_date[2]) || $correct_date[2] < 1000 || strlen(trim($correct_date[0])) !== 2 || strlen(trim($correct_date[1])) !== 2) {                          
                          $message = $bothelper->bagMessageDateFormat();
                          $chat = TelegraphChat::find(1);
                          $chat->message($message)->send();
                          unset($full_data);
                        } else { 
                              if(!checkdate($correct_date[1],$correct_date[0],$correct_date[2])) {                               
                                $message = $bothelper->bagMessageDateFormat();
                                $chat = TelegraphChat::find(1);
                                $chat->message($message)->send();
                                unset($full_data);
                              } else { 
// результат, прошедший проверку
                    
                               $result = $bothelper->editResult($date_of_birth, $username_tg, $fullname, $office_number, $test_group_id);

                               if($result == true) {

                                $message = 'Данные пользователя '. $username_tg." обновлены.";
                                $chat = TelegraphChat::find(1);
                                $chat->message($message)->send();
                                Log::debug($message);
                                unset($full_data); 
                               }     
                               if($result == false) {                          
                                $message = $bothelper->bagMessageUnknownUser($username_tg);
                                $chat = TelegraphChat::find(1);
                                $chat->message($message)->send();
                                unset($full_data); 
                               }
                          
      }
    }
  }
}
              
              break;
// команда admin

              case ($type === 'bot_command' && str_contains($text,'/admin')) :

                  
                  $textArr = explode(PHP_EOL, $text);
             
                  // проверка на количество элементов в массиве
                                if(count($textArr) < 5 || count($textArr) > 5 || $textArr[1] == '' || $textArr[2] == ''|| $textArr[3] == ''|| $textArr[4] == '') {
                                  $message = $bothelper->bagMessageFormatAdmin();
                                  $chat = TelegraphChat::find(1);
                                  $chat->message($message)->send();
                                  unset($full_data);
                                } else {              
                                  $fullname =  $textArr[2];
                                  $date_of_birth = $textArr[3];
                                  $office_number = $textArr[4];
                                  $username_tg = $textArr[1];
                                  
                    // `проверка на соответствие элементов массива              
                                  if(!preg_match('/(\d+)/s', $date_of_birth) || !str_contains($date_of_birth,'.')) {
                                    $message = $bothelper->bagMessageFormatAdmin();
                                    $chat = TelegraphChat::find(1);
                                    $chat->message($message)->send();
                                    unset($full_data);
                  // проверка на буквы в номере офиса
                                  } elseif(!is_numeric($office_number)) {
                                    $message = $bothelper->bagMessageOfficeNumberFormatAdmin();
                                    $chat = TelegraphChat::find(1);
                                    $chat->message($message)->send();
                                    unset($full_data);
                                  } else {
                                
                  // работа с датой 
                                          $correct_date = explode("-", str_replace(".", "-", $date_of_birth));
                                          if(!is_numeric($correct_date[0]) || !is_numeric($correct_date[1]) || !is_numeric($correct_date[2]) || $correct_date[2] < 1000 || strlen(trim($correct_date[0])) !== 2 || strlen(trim($correct_date[1])) !== 2) {                          
                                            $message = $bothelper->bagMessageDateFormatAdmin();
                                            $chat = TelegraphChat::find(1);
                                            $chat->message($message)->send();
                                            unset($full_data);
                                          } else { 
                                                if(!checkdate($correct_date[1],$correct_date[0],$correct_date[2])) {                               
                                                  $message = $bothelper->bagMessageDateFormatAdmin();
                                                  $chat = TelegraphChat::find(1);
                                                  $chat->message($message)->send();
                                                  unset($full_data);
                                                } else { 
                  // результат, прошедший проверку

                                                    $result = $bothelper->adminResult($date_of_birth, $username_tg, $fullname, $office_number, $test_group_id, $full_data);
                                                  if($result == true) {
                                                     $message = $bothelper->messageAdminResult($username_tg);
                                                     $chat = TelegraphChat::find(1);
                                                     $chat->message($message)->send();    
                                                     unset($full_data); 
                }                                                                                                                                 
              }
            }
          }
        }
    break;
      }
    } 
  }
}



