<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class Logger {
    private static function sendMessageTelegram(string $message){
        $curl = new \Curl\Curl();
        $curl->get(URL_TELEGRAM_BOT.API_KEY_TELEGRAM_BOT.'/sendMessage?chat_id='.CHAT_ID_MAIN_TELEGRAM.'&text='.$message);
        $curl->close();
    }

    public static function debug($message){
        Log::channel('stack')->debug($message);
    }

    public static function error($message){
        Log::channel('stack')->error($message);
        self::sendMessageTelegram($message);
    }
}