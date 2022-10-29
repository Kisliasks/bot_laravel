<?php

namespace App\Http\Controllers;

use App\Helpers\Bothelper;
use App\Helpers\Telegram;
use App\Models\Birthday;
use App\Models\MessageId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Http;


class BirthdayController extends Controller
{
   

   public function birthday() {

    $group = TelegraphChat::find(1);
    $test_group_id = $group->chat_id;  // тестовый чат

   
    $date_day = date('d', strtotime('+3 day'));
    $date_month = date('m', strtotime('+3 day'));
   
    //выбираем человека с предстоящим др
    $user = Users::select('username', 'telegram_id', 'date_of_birth', 'fullname', 'office_number')->whereMonth('date_of_birth', $date_month)->whereDay('date_of_birth', $date_day)->get();
  foreach($user as $val) {
    $birth_tg_id = $val->telegram_id;
    $result_date = $val->date_of_birth;
    $result_user = $val->username;
    $result_fullname = $val->fullname;
    $result_office_number = $val->office_number;
    $correct_date = $val->getTransactionDateAttribute($val->date_of_birth);
  }
   // если он есть
     if(!empty($result_date)) {

      $message = 'У пользователя '.$result_user.' '.$result_fullname.' '.$correct_date.' '.$result_office_number.' скоро день рождения! Скинемся на подарок?'.PHP_EOL . 'Если вы уже перешли по ссылке и произвели оплату, нажмите "+". Если отказываетесь, нажмите "-".';
      
      // выбираем всех, у кого нет предстоящего др и есть полные данные в базе 
      $other_users = Users::select('username', 'telegram_id')->whereMonth('date_of_birth', '!=', $date_month)->whereDay('date_of_birth', '!=', $date_day)->get();
      foreach($other_users as $other_user) {
        $tg_id = $other_user->telegram_id;
        // преобразовываем их в id для отправки кнопок
        $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$tg_id)->get();
        foreach($db_chat_id as $db) {
         
        $payment_url = '';
          // отправляем кнопки
          $response = Telegraph::chat(TelegraphChat::find($db->id))->message($message)   // поменять find на $db->id   или на 1
          ->keyboard(Keyboard::make()->buttons([
                  Button::make('Ссылка на оплату')->url($payment_url),
                  Button::make('+')->action('Да.др')->param('birth_user_id',  $birth_tg_id),
                  Button::make('-')->action('Нет.др')->param('birth_user_id',  $birth_tg_id),
                  
          ]))->send();
          
         echo 'Сообщение отправлено в тестовый чат '.$test_group_id.PHP_EOL, "<br>";  // тестовый чат
         echo 'Сообщение массово отправлено в чаты: ';
         echo $tg_id.', ';  // массовые 

        //фиксируем message_id 
          $message_id = $response->telegraphMessageId();
        
          json_decode($message_id);
        //  echo $message_id.PHP_EOL;
          // если человек активировал бота и сообщение отправилось
          if(!empty($message_id)) {
            // вставить данное сообщение в базу с пометкой, в какой чат отправилось сообщение 
          MessageId::insert([
            'message_id' => $message_id,
            'type' => 'birthday',
            'chat_id' => $tg_id    // поменять на групповой чат '-1001813201867' (если слали в сообщение в чат) для теста или на $tg_id для массовой рассылки
          ]);

          // добавляем в таблицу birthday новую связку 

          Birthday::updateOrcreate(          
            ['another_user_id' => $tg_id ],
            ['birth_user_id' => $birth_tg_id]
          );


          }
       
      }
     }
   }

  
  }

  public function birthdayTimeOut() {
    // данный метод будет работать только тогда, когда наступил день рождения человека и в базе есть отосланные сообщения о его др, на которые не ответили
    //он должен запускаться каждый день, а соответствие найдется в коде
    $date_day = date('d');
    $date_month = date('m');
    // получаем всех, кому было отправлено сообщение о др
    $user = Users::select('username', 'telegram_id')->whereMonth('date_of_birth','!=', $date_month)->whereDay('date_of_birth','!=', $date_day)->get();    
    foreach($user as $val) {
      $not_birthday_user_id = $val->telegram_id;
    }
    // получаем того, кого упомянули в сообщении о др
    $user = Users::select('username', 'telegram_id')->whereMonth('date_of_birth', $date_month)->whereDay('date_of_birth', $date_day)->get();    
    foreach($user as $val) {
    $birthday_username = $val->username;
    $birthday_user_id = $val->telegram_id;
    }
    // получаем сообщения, отправленные массовой рассылкой о др, которые пришли всем людям, у кого сегодня не др
    $message_id = MessageId::where([['type', 'birthday'],['chat_id', $not_birthday_user_id]])->get(); // для теста использовать тестовую группу или $not_birthday_user_id
     foreach($message_id as $val) {
     

      //получаем id этих чатов для работы с редактированием сообщений
      $db_chat_id = TelegraphChat::select('id')->where('chat_id' ,$val->chat_id)->get();
       foreach($db_chat_id as $db) {
        if(!empty($birthday_user_id)) {
          //вставляем в find эти чаты 
         $chat = TelegraphChat::find($db->id); // для теста использовать 1 . для массовой использовать $db->id
         //проводим редакцию
         $chat->edit($val->message_id)->message("Вы отказались делать перевод средств на подарок ".$birthday_username)->send();
        }
        echo 'Сообщения о др отосланы из чатов: ';
        echo $val->chat_id.', ';

        // удаляем из базы сообщения с поздравлением человека
      $message = MessageId::where([['type', 'birthday'],['chat_id', $not_birthday_user_id]]);  // для теста использовать тест группу '-1001813201867', для массовой $not_birthday_user_id
       $message->delete();
     }
     // выбираем ччеловека, у которого сегодня др 
     $addNotBirthdayStatus = Birthday::where([['status', null], ['birth_user_id', $birthday_user_id ]])->orWhere([['status', ''],['birth_user_id', $birthday_user_id ]]);
    
     // и устанавливаем статус того, кто не скинулся человеку на др
     $addNotBirthdayStatus->update([
       'status' => 'Отказался'
     ]);
   
 
    }
    }


    public function __invoke(Bothelper $bothelper) {
      // сообщения должны приходить администратору а не в тестовую группу!
   
      $date_day = date('d');
      $date_month = date('m');
      $user = Users::select('username', 'telegram_id', 'fullname', 'date_of_birth', 'office_number')->whereMonth('date_of_birth', $date_month)->whereDay('date_of_birth', $date_day)->get();    
      foreach($user as $val) {
      $birthday_user_id = $val->telegram_id;
      $birthday_username = $val->username;
      $birthday_date_of_birth = $val->getTransactionDateAttribute($val->date_of_birth);
      $birthday_fullname = $val->fullname;
      $birthday_office_number = $val->office_number;
      
    $users_bad_resp_birthday = $bothelper->badResponseBirthdayUsers($birthday_user_id);
    $users_good_resp_birthday = $bothelper->goodResponseBirthdayUsers($birthday_user_id);

      }
      $users_bad_resp_birthday = implode("",$users_bad_resp_birthday);
      $users_good_resp_birthday = implode("",$users_good_resp_birthday);
     
    $users = Users::select('telegram_id')->where('is_admin', 1)->get();
    foreach($users as $user) {
      $telegram_id = $user->telegram_id;
    $db_chat_id = TelegraphChat::select('id')->where('chat_id', $telegram_id)->get();     // вывод всех админов в группе
    foreach($db_chat_id as $db) {
 

    if(!empty($users_bad_resp_birthday) && !empty($users_good_resp_birthday)) {

     

      $message = 'Cтатистика сборов дня рождения '.PHP_EOL.$birthday_username.' '.$birthday_fullname.' '. $birthday_date_of_birth. ' '.$birthday_office_number .PHP_EOL.PHP_EOL.
      'Скинулись:'.PHP_EOL.$users_good_resp_birthday.PHP_EOL.
      'Не скинулись'.PHP_EOL.$users_bad_resp_birthday;

      $chat = TelegraphChat::find($db->id);
      $chat->message($message)->send();  
   
    }
    if(empty($users_bad_resp_birthday) && !empty($users_good_resp_birthday)) {


      $message = 'Cтатистика сборов дня рождения '.PHP_EOL.$birthday_username.' '.$birthday_fullname.' '. $birthday_date_of_birth. ' '.$birthday_office_number .PHP_EOL.PHP_EOL.
      'Скинулись:'.PHP_EOL.$users_good_resp_birthday;
     

      $chat = TelegraphChat::find($db->id);
      $chat->message($message)->send();  
    }
    if(empty($users_good_resp_birthday) && !empty($users_bad_resp_birthday)) {

    

      $message = 'Cтатистика сборов дня рождения '.PHP_EOL.$birthday_username.' '.$birthday_fullname.' '. $birthday_date_of_birth. ' '.$birthday_office_number .PHP_EOL.PHP_EOL.
      'Не скинулись:'.PHP_EOL.$users_bad_resp_birthday;

      $chat = TelegraphChat::find($db->id);    // сменить
      $chat->message($message)->send();  
    }
    if(empty($users_good_resp_birthday) && empty($users_bad_resp_birthday)) {

      $message = 'Нет статистики по сборам на день рождения '.PHP_EOL.$birthday_username.' '.$birthday_fullname.' '. $birthday_date_of_birth. ' '.$birthday_office_number ;
      $chat = TelegraphChat::find($db->id);
      $chat->message($message)->send();  
      
    }
    }
   }
  }

}
