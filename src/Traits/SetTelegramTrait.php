<?php

namespace lex19\Telegram\Traits;

use lex19\Telegram\Telegram;

/**
 *
 */
trait SetTelegramTrait
{
    public function setTelegram(Telegram $telegram): self
    {
        $this->telegram = $telegram;

        return $this;
    }

    public function getTelegram(): Telegram|null
    {
        return $this->telegram;
    }
}
