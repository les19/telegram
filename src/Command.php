<?php

namespace lex19\Telegram;

use lex19\Telegram\Traits\SetTelegramTrait;

abstract class Command
{
    use SetTelegramTrait;

    /**
     * @var Telegram
     */
    protected Telegram $telegram;


    /**
     * @var BaseReception|null
     */
    protected $reception;

    public function __construct(Telegram $telegram = null, BaseReception $reception = null)
    {
        $this->telegram = $telegram;
        $this->reception = $reception;
    }

    public function setNext(array $data): void
    {
        $this->telegram->memory->setNext($data);
    }

    public function setReception(BaseReception $reception)
    {
        $this->reception = $reception;

        return $this;
    }

    public function getReception(): BaseReception|null
    {
        return $this->reception;
    }
}
