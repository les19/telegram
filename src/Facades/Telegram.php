<?php

namespace lex19\Telegram\Facades;

use lex19\Telegram\Telegram as TelegramClass;
use Illuminate\Support\Facades\Facade;

class Telegram extends Facade
{
  protected static function getFacadeAccessor()
  {
    return TelegramClass::class;
  }
}
