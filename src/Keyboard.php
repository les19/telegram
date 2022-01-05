<?php

namespace lex19\Telegram;

abstract class Keyboard
{
    protected $type = 'keyboard';

    final public function get(): array|string
    {
        return $this->keyboard();
    }

    final public function getType(): string
    {
        return $this->type;
    }

    abstract public function keyboard(): array|string;
}
