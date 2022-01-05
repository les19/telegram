<?php

namespace lex19\Telegram\Facades;

use Illuminate\Support\Facades\Facade;
use lex19\Telegram\Telegram as TelegramClass;

class Telegram extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TelegramClass::class;
    }
}
