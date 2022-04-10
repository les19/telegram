<?php

namespace lex19\Telegram;

use lex19\Telegram\Traits\SetTelegramTrait;

abstract class BaseReception
{
    use SetTelegramTrait;

    protected $telegram;
    private $runCommand;

    protected $commands = [];

    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }

    public function call()
    {
        $this->takeCall();
    }

    private function takeCall()
    {
        $this->init();

        $command = $this->find();
        
        $this->fire($command);
    }

    abstract public function init();

    private function fire(array|string|object $command = null): void
    {
        if (is_null($command)) {
            $command = $this->command;
        }
        if (is_array($command) && ! empty($command) && isset($command[0]) && isset($command[1])) {
            $class = array_shift($command);
            $func = array_shift($command);

            if (is_object($class)) {
                $obj = $class;
            } else {
                $obj = app()->make($class, ['telegram' => $this->telegram, 'reception' => $this]);
            }

            if (method_exists($obj, 'setTelegram') && method_exists($obj, 'getTelegram') && $obj->getTelegram() === null) {
                $obj = $obj->setTelegram($this->telegram);
            }
            if ($obj->getReception() === null) {
                $obj = $obj->setReception($this);
            }

            if (method_exists($obj, $func)) {
                $obj->$func(...$command);
            }
        } elseif (is_string($command) && class_exists($command)) {
            $class = $command;

            $obj = app()->make($class, ['telegram' => $this->telegram, 'reception' => $this]);
            if (method_exists($obj, 'setTelegram') && method_exists($obj, 'getTelegram') && $obj->getTelegram() === null) {
                $obj = $obj->setTelegram($this->telegram);
            }
            if ($obj->getReception() === null) {
                $obj = $obj->setReception($this);
            }

            $obj->handle();
        } elseif (is_object($command)) {
            $obj = $command;
            if (method_exists($obj, 'setTelegram') && method_exists($obj, 'getTelegram') && $obj->getTelegram() === null) {
                $obj = $obj->setTelegram($this->telegram);
            }
            if ($obj->getReception() === null) {
                $obj = $obj->setReception($this);
            }

            $obj->handle();
        } else {
            $this->default();
        }
    }

    private function getRunCommand()
    {
        return $this->runCommand;
    }

    public function run($command)
    {
        $this->runCommand = $command;
    }

    public function add(array|string $key, array|string $command = null): self
    {
        if (is_array($key)) {
            $this->commands = array_merge($this->commands, $key);
        } elseif ($command !== null) {
            $this->commands = array_merge($this->commands, [$key => $command]);
        } else {
            $this->commands = array_merge($this->commands, $key);
        }

        return $this;
    }

    private function find()
    {
        if ($this->runCommand !== null) {
            
            return $this->getRunCommand();

        } elseif ($this->telegram->memory->hasDialog()) {
            
            return $this->telegram->memory->getDialog();
        
        } elseif ($this->telegram->memory->hasNext()) {
            
            return $this->telegram->memory->getNext();
        
        } else {
        
            $text = $this->telegram->text;
        
            if (in_array($text, array_keys($this->commands))) {
                return $this->commands[$text];
            }
        
        }

        return [];
    }

    public function default()
    {
        $this->telegram->send('no comprente');
    }
}
