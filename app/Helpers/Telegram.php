<?php 

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Telegram {

    protected $http;
    protected $bot = '5133760433:AAEfcDUm_OAzHa3ipLJQSC-561HQe3UXx_4';
    const url = 'https://api.telegram.org/bot';

    public function __construct(Http $http)
    {
        $this->http = $http;
       
    }

    public function sendMessage($chat_id, $message) {

     return    $this->http::post(self::url.$this->bot.'/sendMessage',[
            'chat_id' => $chat_id,
            'text' =>$message,
            'parse_mode' => 'html'
    
        ]);
    }

    public function editMessage($chat_id, $message, $message_id) {

        return    $this->http::post(self::url.$this->bot.'/editMessageText',[
               'chat_id' => $chat_id,
               'text' =>$message,
               'parse_mode' => 'html',
               'message_id' => $message_id
       
           ]);
       }

    public function sendDocument($chat_id, $file, $reply_id = null) {

      return  $this->http::attach('document', Storage::get('/public/'.$file), 'document.jpg')
        ->post(self::url.$this->bot.'/sendDocument',[
            'chat_id' => $chat_id,
            'reply_to_message_id' => $reply_id,
    
        ]);
    }

    public function sendButtons($chat_id, $message, $button) {

        return    $this->http::post(self::url.$this->bot.'/sendMessage',[
               'chat_id' => $chat_id,
               'text' =>$message,
               'parse_mode' => 'html',
               'reply_markup' => $button
       
           ]);
       }

       public function editButtons($chat_id, $message, $button, $message_id) {

        return    $this->http::post(self::url.$this->bot.'/editMessageText',[
            'chat_id' => $chat_id,
            'text' =>$message,
            'parse_mode' => 'html',
            'reply_markup' => $button,
            'message_id' => $message_id
    
        ]);
       }

       public function getWebhookData() {
      return $update = json_decode(file_get_contents('php://input'));
      

       }


}

// 583801982
// (string)view('report', $data)
?>