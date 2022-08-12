<?php

namespace lex19\Telegram;

use lex19\Telegram\Traits\SetTelegramTrait;

abstract class Dialog extends Command
{

    protected $order = [];  
    
    
    public function getOrder(): array
    {
        return $this->order;
    }


}
