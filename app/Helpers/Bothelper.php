<?php 

namespace App\Helpers;

use App\Models\Birthday;
use App\Models\Users;
use App\Models\Workstatus;
use DateTime;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Bothelper {

    public function info_bot() {

        $message = 'Команда <b>/info</b>:'.PHP_EOL.'выводит всю информацию о работе бота.'.PHP_EOL.PHP_EOL.
        'Команда <b>/edit</b>:'.PHP_EOL.'позволяет добавить полную информацию о новом участнике группы. <i>Шаблон</i>:'
        .PHP_EOL.'/edit'.PHP_EOL.'@username'.PHP_EOL.'Фамилия Имя Отчество(если есть)'.PHP_EOL.'дд.мм.гггг'.PHP_EOL.'номер офиса'.
        PHP_EOL.PHP_EOL.
        'Команда <b>/all</b>:'.PHP_EOL.'выводит информацию об участниках группы. Если пользователь не имеет полной информации о себе, команда выведет его @username'.PHP_EOL.PHP_EOL.
        'Команда <b>/admin</b>:'.PHP_EOL.'добавляет информацию об администраторе группы. Необходимо воспользоваться данной командой, если вы являетесь администратором и создателем группы. <i>Шаблон</i>:'.
         PHP_EOL.'/edit'.PHP_EOL.'@username(ваш)'.PHP_EOL.'Фамилия Имя Отчество(если есть)'.PHP_EOL.'дд.мм.гггг'.PHP_EOL.'номер офиса'.PHP_EOL.PHP_EOL.
        'Команда <b>/new_admin</b>:'.PHP_EOL.'дает права администратора любому участнику группы. <i>Шаблон</i>:'.
         PHP_EOL.'/edit'.PHP_EOL.'@username'.PHP_EOL.PHP_EOL.
        '❗Для заполнения поля @username в команде /admin скопируйте его из настроек своего аккаунта вручную.'.PHP_EOL.PHP_EOL.
        '<b> ‼️Важно!</b> Для полноценного выполнения функционала бота каждый участник группы должен начать с ним личную беседу @office_msc_bot, нажав start.';
        
        return $message;
    }

   public function bugMessageNewAdmin() {

    $message = 'Некорректный формат команды /new_admin. Используйте шаблон с переносом строки для каждого значения:'.PHP_EOL. '/edit'.PHP_EOL.'@username';
    return $message;
   }

    public function bagMessageFormat() {

        $message = 'Некорректный формат команды /edit. Используйте шаблон с переносом строки для каждого значения:'.PHP_EOL. '/edit'.PHP_EOL.'@username'.PHP_EOL.'Фамилия Имя Отчество(если есть)'.PHP_EOL.'дд.мм.гггг'.PHP_EOL.'номер офиса'.PHP_EOL;
        
        return $message;
    }

    public function bagMessageFormatAdmin() {

        $message = 'Некорректный формат команды /admin. Используйте шаблон с переносом строки для каждого значения:'.PHP_EOL. '/edit'.PHP_EOL.'@username'.PHP_EOL.'Фамилия Имя Отчество(если есть)'.PHP_EOL.'дд.мм.гггг'.PHP_EOL.'номер офиса'.PHP_EOL;
        
        return $message;
    }

    public function bagMessageOfficeNumberFormat() {

        $message = 'Неправильный формат значений для команды /edit. Номер офиса должен содержать только цифры. Используйте шаблон с переносом строки для каждого значения:'.PHP_EOL.'/edit'.PHP_EOL.'@username'.PHP_EOL.'Фамилия Имя Отчество(если есть)'.PHP_EOL.'дд.мм.гггг'.PHP_EOL.'номер офиса'.PHP_EOL;
        
        return $message;
    }

    public function bagMessageOfficeNumberFormatAdmin() {

        $message = 'Неправильный формат значений для команды /admin. Номер офиса должен содержать только цифры. Используйте шаблон с переносом строки для каждого значения:'.PHP_EOL.'/edit'.PHP_EOL.'@username'.PHP_EOL.'Фамилия Имя Отчество(если есть)'.PHP_EOL.'дд.мм.гггг'.PHP_EOL.'номер офиса'.PHP_EOL;
        
        return $message;
    }

    public function bagMessageDateFormat() {

        $message = 'Неправильный формат даты для команды /edit. Ожидается дд.мм.гггг';
        
        return $message;
    }

    public function bagMessageDateFormatAdmin() {

        $message = 'Неправильный формат даты для команды /admin. Ожидается дд.мм.гггг';
        
        return $message;
    }

    public function bagMessageCountElement() {

        $message = 'Превышено допустимое количество значений в одной из строк. Воспользуйтесь следующим форматом'.PHP_EOL.'/edit'.PHP_EOL.'@username (одно значение)'.PHP_EOL.'Фамилия Имя Отчество (три или меньше значений)'.PHP_EOL.'дд.мм.гггг (одно значение)'.PHP_EOL.'номер офиса (одно значение)'.PHP_EOL;
        
        return $message;
    }

    public function bagMessageUnknownUser($username_tg) {

        $message = 'Пользователь '.$username_tg.' не состоит в этой группе. Проверьте правильность написания @username в команде /edit.';
        
        return $message;
    }
    public function unknownUserForNewAdmin($username_tg) {

      $message = 'Пользователь '.$username_tg.' не состоит в этой группе. Проверьте правильность написания @username в команде /new_admin. Для вывода всех участников воспользуйтесь командой /all';
      
      return $message;
  }
    public function messageAdminResult($username_tg) {

        $message = 'Добавлены данные администратора '.$username_tg.PHP_EOL.'Проверьте правильность написания вашего @username, нажав на него: '.$username_tg.PHP_EOL.
        'Если Вам не удалось перейти по ссылке '.$username_tg.', исправьте ваш @username, повторно вызвав команду /admin';

        return $message;
    }

    public function notUsersAllComand() {

        $message = 'В группе пока нет участников. Вы можете добавить пользователей прямо сейчас.'.PHP_EOL.PHP_EOL.
        'Для получения полной информации используйте команду /info';

        return $message;
    }



// обработчики команд ////////////////////////////////////////////////////////////////////////////


public function newAdminStatus($username_tg) {

  $db_user = Users::select('username')->where('username', $username_tg)->get();
  foreach($db_user as $user) {
    if(isset($user->username)) {
        

$user = Users::where('username', $user->username);
$user->update([
  'is_admin' => 1
]);
return true;
    }
  }

if(!isset($user->username)) {                                    
  return false;
}


}


public function selectAllUsers() {

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
  
           $usr = implode("", $usernameArr);
           return $usr;

}

public function editResult($date_of_birth, $username_tg, $fullname, $office_number, $test_group_id) {

    $date = new DateTime($date_of_birth);
    $date->format('Y-m-d');                                      
        
      $db_user = Users::select('username', 'fullname', 'date_of_birth', 'office_number')->where('username', $username_tg)->get();
          foreach($db_user as $user) {
            if(isset($user->username)) {
                

                $db_user = Users::where('username',$user->username); 
                $db_user->update([
  
                  'fullname' => $fullname,
                  'date_of_birth' => $date->format('Y-m-d'),
                  'office_number' => $office_number
                ]);
              
                    return true;
            } 
          }

          if(!isset($user->username)) {                                    
                return false;
          }
    }

    public function adminResult($date_of_birth, $username_tg, $fullname, $office_number, $test_group_id, $full_data) {

        $date = new DateTime($date_of_birth);
        $date->format('Y-m-d');                                      
            
                                                            
            Users::updateOrcreate(
            ['telegram_id' => $full_data['from']['id']], 
            ['username' => $username_tg,
            'fullname' => $fullname,
            'date_of_birth' => $date->format('Y-m-d'),
            'office_number' => $office_number,
            'is_admin' => 1]
            );
            TelegraphChat::updateOrcreate(
              ['chat_id' => $full_data['from']['id']],
              ['name' => $username_tg]

            );

            return true;
    }





    // обработка ответа рабочего дня ////////////////////////////////

  
    public function insertWorkStatus($status, $username) {


        $work_user = Users::where('username',$username); 
        $work_user->update([

        'work_status' => $status
        ]);

    }


    public function selectNotWorkUsers() {

        $db_users = Users::select('username', 'fullname', 'date_of_birth', 'office_number')->where('work_status', 'Нет')->get();
                $usernameArr = [];
                
                foreach($db_users as $users) {   
                  if(isset($users->date_of_birth)) 
                  { 
                    $correct_date = $users->getTransactionDateAttribute($users->date_of_birth);
                  
                      array_push($usernameArr, $users->username.' '. $users->fullname.' '.$correct_date .' '.$users->office_number.PHP_EOL);       
                  } else {
                    array_push($usernameArr, $users->username.' '.$users->fullname.' '.$users->date_of_birth.' '.$users->office_number.PHP_EOL); 
                  }                   
                }
      
               $usr = implode("", $usernameArr);
               return $usr;
    
    }

    public function selectWorkUsers() {

        $db_users = Users::select('username', 'fullname', 'date_of_birth', 'office_number')->where('work_status', 'Да')->get();
                $usernameArr = [];
                
                foreach($db_users as $users) {   
                  if(isset($users->date_of_birth)) 
                  { 
                    $correct_date = $users->getTransactionDateAttribute($users->date_of_birth);
                  
                      array_push($usernameArr, $users->username.' '. $users->fullname.' '.$correct_date .' '.$users->office_number.PHP_EOL);       
                  } else {
                    array_push($usernameArr, $users->username.' '.$users->fullname.' '.$users->date_of_birth.' '.$users->office_number.PHP_EOL); 
                  }                   
                }
      
               $usr = implode("", $usernameArr);
               return $usr;
    
    }

    ///////  обработка ответа дня рождения 

    public function badResponseBirthdayUsers($birthday_user_id) {
      $users_bad_resp_birthday[] = '';
      
      $result = Birthday::select('another_user_id')->where([['status', 'Отказался'],['birth_user_id', $birthday_user_id]])->get();
      foreach($result as $res) {
      
        $db_users = Users::where('telegram_id', $res->another_user_id)->get();
      foreach($db_users as $users) {
        $users_bad_resp_birthday[] = $users->username.' '. $users->fullname.PHP_EOL;
      }
     
      }
      return $users_bad_resp_birthday;
  }

    public function goodResponseBirthdayUsers($birthday_user_id) {

      $users_good_resp_birthday[] = '';
      $result = Birthday::select('another_user_id')->where([['status', 'Оплатил'],['birth_user_id', $birthday_user_id]])->get();
      foreach($result as $res) {
      
        $db_users = Users::where('telegram_id', $res->another_user_id)->get();
      foreach($db_users as $users) {
        $users_good_resp_birthday[] = $users->username.' '. $users->fullname.PHP_EOL;
      }
      
      }
      return $users_good_resp_birthday;
     
    }
}




?>