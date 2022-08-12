<?php

namespace lex19\Telegram;

use Illuminate\Support\Facades\Cache;

class Memory
{
    private $telegram;
    public $data;
    public $key;
    public $last;
    public $current;
    public $dialog;

    private $state;
    private $storage = [];
    private $next;

    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
        $this->init();
    }

    private function init()
    {
        $this->key = $this->telegram->chat_id;
        $this->data = Cache::get($this->key);

        $this->current = $this->telegram->text;

        if ($this->data !== null) {
            foreach (['last', 'state', 'storage', 'next', 'dialog'] as $value) {
                if (isset($this->data[$value])) {
                    $this->$value = $this->data[$value];
                }
            }
        }
    }

    public function save()
    {
        Cache::put($this->key, [
            "last" => $this->current,
            "state" => $this->state,
            "next" => $this->next,
            "dialog" => $this->dialog,
            "storage" => $this->storage,
        ]);
    }

    public function getState()
    {
        return $this->state;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getNext()
    {
        $next = $this->next;
        $this->next = null;

        return $next;
    }

    public function hasNext(): bool
    {
        return $this->next !== null;
    }

    public function hasDialog(): bool
    {
        return $this->dialog !== null;
    }

    public function getDialog(): array
    {

        if($this->hasDialog()){

            $command = [
                $this->dialog['class'],
                $this->dialog['method']
            ];

            $this->dialogStep();

            return $command;

        }

        return [];
    }

    public function setNext($next)
    {
        return $this->next = $next;
    }

    public function setDialog(Dialog $dialog)
    {
        $order = $dialog->getOrder();

            return $this->dialog = [
            "class" => get_class($dialog),
            "method" => $order[0],
            "order" => $order,
            "index" => 0, 
            "next" => (count($order) > 1 ? $order[1] : false)
        ];
    }

    private function dialogStep()
    {
        if($this->hasDialog()){
            if($this->dialog["next"]){
                $this->dialog["method"] = $this->dialog["next"];
                $this->dialog["index"]++;
                
                if(isset($this->dialog["order"][$this->dialog["index"] + 1])){
                    $this->dialog["next"] = $this->dialog["order"][$this->dialog["index"] + 1];
                } else {
                    $this->dialog["next"] = false;
                }
            } else {
                $this->dialog = null;
            }
        } 
    }

    public function setStorage(array|string $value)
    {
        $storage = $this->storage;

        if ($storage !== null && is_array($storage) && is_array($value)) {
            foreach ($value as $key => $data) {
                $this->storage[$key] = $data;
            }
        } else {
            $this->storage = $value;
        }
    }
}
