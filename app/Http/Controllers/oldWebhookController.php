<?php

namespace App\Http\Controllers;

use App\Helpers\Bothelper;
use App\Helpers\Telegram;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use DateTime;


class oldWebhookController extends Controller
{
   


    public function index(Request $request, Telegram $telegram, Bothelper $bothelper) {

      $test_group_id = '-1001813201867';

      $username = $request->input('message');
      Log::debug('в request пусто');
      
      if(!empty($username)) {
      Log::debug('он все же добрался до switch case');

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
            Log::debug('и выполнил команду добавления нового пользователя');
            break;
          case isset($username['left_chat_member']) :
            $left_member = $username;
            $group_user_name = '@'.$left_member['left_chat_member']['username'];
            Log::debug('он добрался до удаления пользователя из базы');
        $user = Users::where('username', $group_user_name);
        if(isset($user)) {
          $user->delete();

          $message = 'Пользователь '. $group_user_name. " вышел из группы";
          $telegram->sendMessage($test_group_id, $message);
          unset($left_member);
          Log::debug('он смог благополучно удалить его');
        }
            break;
        }
      
      }
// обработка команд ===================================================

     if(isset($username['entities'])) {
        
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
            $db_users = Users::select('username', 'fullname', 'date_of_birth', 'office_number')->get();
            $usernameArr = [];
            foreach($db_users as $users) {   
              if(isset($users->date_of_birth)) 
              { 
                $correct_date = $users->getTransactionDateAttribute($users->date_of_birth);
              
                  array_push($usernameArr, $users->username.' '.str_replace("_", " ", $users->fullname).' '.$correct_date .' '.$users->office_number.PHP_EOL);       
              } else {
                array_push($usernameArr, $users->username.' '.str_replace("_", " ", $users->fullname).' '.$users->date_of_birth.' '.$users->office_number.PHP_EOL); 
              }                   
            }
  
           $usr = implode(" ", $usernameArr);
          $message = '<b>Пользователи в базе:</b> '.PHP_EOL. $usr;
          $telegram->sendMessage($test_group_id, $message);
          unset($full_data);
          Log::debug('произошел вывод всех пользователей из базы');
            break;
          

            case ($type === 'bot_command' && str_contains($text,'/edit')) :

              $textArr = explode(" ", $text);
              if(count($textArr) < 5 || count($textArr) > 5) {
// работа с форматом команды
                Log::debug(count($textArr));
                Log::debug($textArr);
                $message = 'Некорректный формат команды /edit. Используйте шаблон с одним пробелом между значениями: /edit @username Имя_необязательное Отчество_Фамилия дд.мм.гггг -номер офиса-'.PHP_EOL.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванович_Иванов 12.05.1992 404'.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванов 12.05.1992 404';
                $telegram->sendMessage($test_group_id, $message);
                unset($full_data);
              } elseif(in_array('', $textArr, true)) {
                Log::debug(count($textArr));
                Log::debug($textArr);
                $message = 'Некорректный формат команды /edit. Используйте шаблон с одним пробелом между значениями: /edit @username Имя_необязательное Отчество_Фамилия дд.мм.гггг -номер офиса-'.PHP_EOL.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванович_Иванов 12.05.1992 404'.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванов 12.05.1992 404';
                $telegram->sendMessage($test_group_id, $message);
                unset($full_data);

              }elseif(count($textArr) < 5 && in_array('', $textArr, true || count($textArr) > 5)) {
                Log::debug(count($textArr));
                Log::debug($textArr);
                $message = 'Некорректный формат команды /edit. Используйте шаблон с одним пробелом между значениями: /edit @username Имя_необязательное Отчество_Фамилия дд.мм.гггг -номер офиса-'.PHP_EOL.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванович_Иванов 12.05.1992 404'.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванов 12.05.1992 404';
                $telegram->sendMessage($test_group_id, $message);
                unset($full_data);

            } else {
  // работа с неправильным порядком значений команды            
              $fullname =  $textArr[2];
              $date_of_birth = str_replace(".", "-", $textArr[3]);
              $office_number = $textArr[4];
              $username_tg = $textArr[1];
           
              if(!preg_match('/(\d+)/s', $date_of_birth)) {
              Log::debug($date_of_birth);

              $message = 'Неправильный порядок значений для команды /edit. Используйте шаблон с одним пробелом между между значениями: /edit @username Имя_необязательное Отчество_Фамилия дд.мм.гггг -номер офиса-'.PHP_EOL.
              PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванович_Иванов 12.05.1992 404'.
              PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванов 12.05.1992 404';
              $telegram->sendMessage($test_group_id, $message);
              unset($full_data);

              } elseif(!str_contains($date_of_birth,'-')) {

                Log::debug('формат неправильный');
                $message = 'Неправильный порядок значений для команды /edit. Используйте шаблон с одним пробелом между между значениями: /edit @username Имя_необязательное Отчество_Фамилия дд.мм.гггг -номер офиса-'.PHP_EOL.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванович_Иванов 12.05.1992 404'.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванов 12.05.1992 404';
                $telegram->sendMessage($test_group_id, $message);
                unset($full_data);

              } elseif(!is_numeric($office_number)) {
                $message = 'Неправильный формат значений для команды /edit. Номер офиса должен содержать только цифры. Используйте шаблон с одним пробелом между между значениями: /edit @username Имя_необязательное Отчество_Фамилия дд.мм.гггг -номер офиса-'.PHP_EOL.
                PHP_EOL.'<i>Пример</i>: /edit @ivantg Иван_Иванович_Иванов 12.05.1992 404'.
                PHP_EOL.'<i>Пример</i>:/edit @ivantg Иван_Иванов 12.05.1992 404';
                $telegram->sendMessage($test_group_id, $message);
                unset($full_data);
              
               } else {
// работа с датой
            
          $correct_date = explode("-", $date_of_birth);
          if(!is_numeric($correct_date[0]) || !is_numeric($correct_date[1]) || !is_numeric($correct_date[2])) {
            Log::debug('Неправильный формат даты');
            $message = 'Неправильный формат даты для команды /edit. Ожидается дд.мм.гггг';
            $telegram->sendMessage($test_group_id, $message);
            unset($full_data);
          } else {
                if(!checkdate($correct_date[1],$correct_date[0],$correct_date[2]) || $correct_date[2] < 1000 || strlen(trim($correct_date[0])) !== 2 || strlen(trim($correct_date[1])) !== 2) {
                  Log::debug('Неправильный формат даты');
                  $message = 'Неправильный формат даты для команды /edit. Ожидается дд.мм.гггг';
                  $telegram->sendMessage($test_group_id, $message);
                  unset($full_data);
              
                } else {
                  $date = new DateTime($date_of_birth);
                    $date->format('Y-m-d');
                    
                
              $db_user = Users::where('username',$username_tg);
                          
                $db_user->update([
        
                'fullname' => $fullname,
                'date_of_birth' => $date->format('Y-m-d'),
                'office_number' => $office_number
              ]);
           
            $message = 'Данные пользователя '. $username_tg." обновлены.";
            $telegram->sendMessage($test_group_id, $message);
            unset($full_data);
                }
            }
            }
          }
              break;
       
        
    

    } 

  }

  }

}