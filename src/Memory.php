<?php

namespace lex19\Telegram;

use lex19\Telegram\Telegram;
use Illuminate\Support\Facades\Cache;

class Memory
{

  private $telegram;
  public $data;
  public $key;
  public $last;
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

      foreach (['last', 'state', 'storage', 'next'] as $value) {
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
      "storage" => $this->storage
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

  public function hasNext()
  {
    return $this->next !== null ? true : false;
  }
  
  public function setNext($next)
  {
    return $this->next  = $next;
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
