<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Your BOT token from telegram bot father
  |--------------------------------------------------------------------------
  */

  "token" => env('TELEGRAM_TOKEN', null),

  /*
  |--------------------------------------------------------------------------
  | Base url telegram api
  |--------------------------------------------------------------------------
  */

  "base" => "https://api.telegram.org/bot",

  /*
  |--------------------------------------------------------------------------
  | Base reception class. If nothing is passed to the Telegram::hook method, then this class will be used
  |--------------------------------------------------------------------------
  */

  "reception" => App\Telegram\Receptions\MainReception::class,

  /*
  |--------------------------------------------------------------------------
  | Other service params.
  |--------------------------------------------------------------------------
  */

  "file_base" => "https://api.telegram.org/file/bot",

  "file_url" => "https://api.telegram.org/file/bot" . env('TELEGRAM_TOKEN', null),

  "url" => "https://api.telegram.org/bot" . env('TELEGRAM_TOKEN', null),

];
