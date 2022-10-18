<?php

namespace App\Http\Controllers;

use App\Helpers\Bothelper;
use App\Helpers\Telegram;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use App\Models\Workstatus;
use DateTime;


class WebhookController extends Controller
{
   
   private $message_id;



    public function __invoke(Request $request, Telegram $telegram, Bothelper $bothelper) {

      $test_group_id = '-1001813201867';
     

      $username = $request->input('message');
      $callback_query = $request->input('callback_query');
      
    

      // готовность к рабочему дню /////////////////////////////////////////
      
      if(!empty($callback_query)) {
        $full_data_buttons = $callback_query;
         $message_id = $full_data_buttons['message']['message_id'];
         $data = $full_data_buttons['data'];
         $username = '@'.$full_data_buttons['from']['username'];

         date_default_timezone_set('Europe/Moscow');
         $time = date('H:i');
               if($time = '14:12') {        
         
           $data_bad_resp = 'блаблабла';
           
           Log::debug($data_bad_resp);
      }
         
                         
        switch($full_data_buttons) {
                        
              case $data == 'Да' :
               
                  $message = 'Вы сделали свой выбор. Хорошего дня!';
                  $buttons = $bothelper->unsetButtonWorkDay();
                  $telegram->editButtons($test_group_id, $message, $buttons, $message_id);
                  $bothelper->insertWorkStatus($data, $username);
                
                unset($full_data_buttons);

              break;

              case !empty($data_bad_resp) || $data == 'Нет' :
                $message = 'Вы сделали свой выбор. Хорошего дня!';
                $buttons = $bothelper->unsetButtonWorkDay();
                $telegram->editButtons($test_group_id, $message, $buttons, $message_id);
                $bothelper->insertWorkStatus($data, $username);

                unset($full_data_buttons);
                break;
          }
        }
/////////////////////////////////////////////////////

       

      ////////////////////////////////////////////////////////////
      
      
      if(!empty($username)) {
      
        switch ($username) {
          case isset($username['new_chat_member']):
            $new_member = $username;
            Users::updateOrcreate(
              ['telegram_id' => $new_member['new_chat_member']['id']], 
              ['username' => "@".$new_member['new_chat_member']['username']]
           );
          
           $message = "В группу вступил пользователь " .'@'.$new_member['new_chat_member']['username'];
           $telegram->sendMessage($test_group_id, $message);
            unset($new_member);
            Log::debug($message);
            break;
          case isset($username['left_chat_member']) :
            $left_member = $username;
            $group_user_name = '@'.$left_member['left_chat_member']['username'];
            
            $user = Users::where('username', $group_user_name);
            
            if(isset($user)) {
              $user->delete();
              

          $message = 'Пользователь '. $group_user_name. " вышел из группы";
          $telegram->sendMessage($test_group_id, $message);
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
          // работа с командой /info

          case ($type === 'bot_command' && str_contains($text,'/info')) :
            
            $info_bot = $bothelper->info_bot();
            $telegram->sendMessage($test_group_id, $info_bot);
            Log::debug('Получение всей информации о работе бота');
            break;

          // работа с командой /all
          case ($type === 'bot_command' && str_contains($text,'/all')) :
            $usr = $bothelper->selectAllUsers();

            if(empty($usr)) {
              $message = $bothelper->notUsersAllComand();
              $telegram->sendMessage($test_group_id, $message);
              unset($full_data);
              Log::debug('В группе пока нет пользователей');
             }
                if(!empty($usr)) {
                  $message = '<b>Пользователи:</b> '.PHP_EOL. $usr;
                $telegram->sendMessage($test_group_id, $message);
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
                $telegram->sendMessage($test_group_id, $message);
                unset($full_data);
              } else {              
                $fullname =  $textArr[2];
                $date_of_birth = $textArr[3];
                $office_number = $textArr[4];
                $username_tg = $textArr[1];
  // `проверка на соответствие элементов массива              
                if(!preg_match('/(\d+)/s', $date_of_birth) || !str_contains($date_of_birth,'.')) {
                  $message = $bothelper->bagMessageFormat();
                  $telegram->sendMessage($test_group_id, $message);
                  unset($full_data);
// проверка на буквы в номере офиса
                } elseif(!is_numeric($office_number)) {
                  $message = $bothelper->bagMessageOfficeNumberFormat();
                  $telegram->sendMessage($test_group_id, $message);
                  unset($full_data);
                } else {
              
// работа с датой 
                        $correct_date = explode("-", str_replace(".", "-", $date_of_birth));
                        if(!is_numeric($correct_date[0]) || !is_numeric($correct_date[1]) || !is_numeric($correct_date[2]) || $correct_date[2] < 1000 || strlen(trim($correct_date[0])) !== 2 || strlen(trim($correct_date[1])) !== 2) {                          
                          $message = $bothelper->bagMessageDateFormat();
                          $telegram->sendMessage($test_group_id, $message);
                          unset($full_data);
                        } else { 
                              if(!checkdate($correct_date[1],$correct_date[0],$correct_date[2])) {                               
                                $message = $bothelper->bagMessageDateFormat();
                                $telegram->sendMessage($test_group_id, $message);
                                unset($full_data);
                              } else { 
// результат, прошедший проверку
                    
                               $result = $bothelper->editResult($date_of_birth, $username_tg, $fullname, $office_number, $test_group_id);

                               if($result == true) {

                                $message = 'Данные пользователя '. $username_tg." обновлены.";
                                $telegram->sendMessage($test_group_id, $message);
                                Log::debug($message);
                                unset($full_data); 
                               }     
                               if($result == false) {                          
                                $message = $bothelper->bagMessageUnknownUser($username_tg);
                                $telegram->sendMessage($test_group_id, $message);
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
                                  $telegram->sendMessage($test_group_id, $message);
                                  unset($full_data);
                                } else {              
                                  $fullname =  $textArr[2];
                                  $date_of_birth = $textArr[3];
                                  $office_number = $textArr[4];
                                  $username_tg = $textArr[1];
                                  
                    // `проверка на соответствие элементов массива              
                                  if(!preg_match('/(\d+)/s', $date_of_birth) || !str_contains($date_of_birth,'.')) {
                                    $message = $bothelper->bagMessageFormatAdmin();
                                    $telegram->sendMessage($test_group_id, $message);
                                    unset($full_data);
                  // проверка на буквы в номере офиса
                                  } elseif(!is_numeric($office_number)) {
                                    $message = $bothelper->bagMessageOfficeNumberFormatAdmin();
                                    $telegram->sendMessage($test_group_id, $message);
                                    unset($full_data);
                                  } else {
                                
                  // работа с датой 
                                          $correct_date = explode("-", str_replace(".", "-", $date_of_birth));
                                          if(!is_numeric($correct_date[0]) || !is_numeric($correct_date[1]) || !is_numeric($correct_date[2]) || $correct_date[2] < 1000 || strlen(trim($correct_date[0])) !== 2 || strlen(trim($correct_date[1])) !== 2) {                          
                                            $message = $bothelper->bagMessageDateFormatAdmin();
                                            $telegram->sendMessage($test_group_id, $message);
                                            unset($full_data);
                                          } else { 
                                                if(!checkdate($correct_date[1],$correct_date[0],$correct_date[2])) {                               
                                                  $message = $bothelper->bagMessageDateFormatAdmin();
                                                  $telegram->sendMessage($test_group_id, $message);
                                                  unset($full_data);
                                                } else { 
                  // результат, прошедший проверку

                                                    $result = $bothelper->adminResult($date_of_birth, $username_tg, $fullname, $office_number, $test_group_id, $full_data);
                                                  if($result == true) {
                                                     $message = $bothelper->messageAdminResult($username_tg);
                                                     $telegram->sendMessage($test_group_id, $message);     
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



