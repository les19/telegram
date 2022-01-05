<?php

return [
  "token" => env('TELEGRAM_TOKEN', null),
  "base" => "https://api.telegram.org/bot",
  "reception" => App\Telegram\Receptions\Reception::class,
  "file_base" => "https://api.telegram.org/file/bot",
  "file_url" => "https://api.telegram.org/file/bot" . env('TELEGRAM_TOKEN', null),
  "url" => "https://api.telegram.org/bot" . env('TELEGRAM_TOKEN', null)
];
