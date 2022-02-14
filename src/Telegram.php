<?php

namespace lex19\Telegram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class Telegram
{
    /**
     * Current Http request
     * @var Illuminate\Http\Request
     */
    public $request;

    public $fromTelegram = false;

    /**
     * Text value of message or callback_query
     * @var string
     */
    public $text;

    /**
     * Data from callback_query 
     * @var array
     */
    public $data;

    /**
     * Previos message id
     * @var int
     */
    public $last_message_id = null;

    /**
     * Telegram chat id
     * @var int
     */
    public $chat_id;

    /**
     * Url to send to telegram
     * @var string
     */
    public $url;

    /**
     * Cache wrapper
     * @var Memory
     */
    public $memory;

    protected $params = [];
    protected $method;
    protected $current_keyboard_type = 'keyboard';
    protected $parse_mode = 'HTML';
    protected $reception;
    protected $callback_query = false;
    protected $keyboard;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->memory = new Memory($this);

        $this->remember();

        if ($this->request !== null && $this->request->route() !== null && $this->request->method() == 'POST') {
            if (isset($this->request['callback_query'])) {
                $this->callback_query = true;
                $this->data = $this->request['callback_query']['data'];
                $this->text = mb_strtolower($request['callback_query']['message']['text']);
                $this->chat_id = $request['callback_query']['message']['chat']['id'];
            } else {
                $this->text = mb_strtolower($request['message']['text']);
                $this->chat_id = $request['message']['chat']['id'];
            }
            $this->fromTelegram = true;
        }

        $this->url = $this->url . config('telegram.url') . '/';
    }

    public function __destruct()
    {
        $this->memory->save();
    }

    private function remember()
    {
        $this->last_message_id = $this->memory->getFromStorage('last_message_id');
    }

    public function send(array|string $data = [], string $method = "sendMessage"): \Illuminate\Http\Client\Response
    {
        if (is_null($this->method)) {
            $this->method = $method;
        }

        if (!is_array($data) && is_string($data)) {
            $data = [
                "text" => $data,
            ];
        }

        if (!isset($data['reply_markup']) && $this->keyboard) {
            $data['reply_markup'] = $this->keyboard;
        }

        if (!isset($data['chat_id']) && $this->chat_id !== null) {
            $data['chat_id'] = $this->chat_id;
        }

        if (!isset($data['parse_mode'])) {
            $data['parse_mode'] = $this->parse_mode;
        }


        $this->params = array_merge($data, $this->params);
        $url = $this->url . $this->method;

        try {
            $response = Http::withoutVerifying()->post($url, $this->params);
        } catch (\Throwable $th) {
            report($th);
            return response();
        }

        if (!$response['ok']) {
            info($response->body());
        } else {
            $this->memory->setStorage([
                "last_message_id" => $response['result']['message_id']
            ]);
        }

        return $response;
    }

    // Remove current reply keyboard
    public function removeKeyboard(): self
    {
        $this->keyboard = json_encode([
            "remove_keyboard" => true,
        ]);
        return $this;
    }

    public function keyboard(Keyboard|string|array $markup, array $keys = null): self
    {
        $keyboard = null;

        // 

        if (gettype($markup) === 'object') {
            $this->current_keyboard_type = $markup->getType();

            $data = $markup->get();
            if (is_array($data)) {
                $keyboard = $this->makeKeyboard(...$data);
            } elseif (is_string($data)) {
                $keyboard = $data;
            }
        } elseif (is_string($markup)) {
            $obj = app()->make($markup);
            $this->current_keyboard_type = $obj->getType();
            $data = $obj->get();

            if (is_array($data)) {
                $keyboard = $this->makeKeyboard(...$data);
            } elseif (is_string($data)) {
                $keyboard = $data;
            }
        } elseif (is_array($markup) && is_array($keys) && !empty($keys) && !empty($markup)) {
            $keyboard = $this->makeKeyboard($markup, $keys);
        }

        // 

        if ($keyboard !== null && is_array($keyboard)) {
            $this->keyboard = json_encode([
                $this->current_keyboard_type => $keyboard,
                "resize_keyboard" => true,
            ]);
        } elseif ($keyboard !== null && is_string($keyboard)) {
            $this->keyboard = $keyboard;
        }

        return $this;
    }

    public function makeKeyboard(array $markup, array $keys): array
    {
        $data = [];
        foreach ($markup as $index => $row) {
            for ($i = 0; $i < $row; $i++) {
                if (count($keys) !== 0) {
                    $key = array_shift($keys);
                    if ($this->current_keyboard_type === 'inline_keyboard' && !isset($key['url']) && !isset($key['callback_data'])) {
                        $key['callback_data'] = $key['text'];
                    }
                    $data[$index][] = $key;
                }
            }
        }

        return $data;
    }

    public function parseMode($mode): self
    {
        $this->parse_mode = $mode;

        return $this;
    }

    public function hook(string|object $reception = null)
    {
        try {
            if (!$this->fromTelegram) {
                $url = URL::current();
                $this->deleteHook()->send();

                return $this->setHook($url)->send();
            } else {
                if (is_string($reception)) {
                    $class = $reception ? $reception : config('telegram.reception');
                    $obj = app()->make($class, [
                        "telegram" => $this,
                    ]);
                } elseif (is_object($reception)) {
                    $obj = $reception;
                }

                if (method_exists($obj, 'setTelegram') && method_exists($obj, 'getTelegram') && $obj->getTelegram() === null) {
                    $obj = $obj->setTelegram($this);
                }
                $obj->call();
            }
        } catch (\Throwable $th) {
            report($th);

            return response();
        }
    }

    public function setHook($url)
    {
        $this->method = 'setWebhook';
        if (!str_contains($url, "https://") && str_contains($url, "http://")) {
            $url = str_replace("http://", "https://", $url);
        }
        $this->params = ['url' => $url];

        return $this;
    }

    public function deleteHook()
    {
        $this->method = 'deleteWebhook';

        return $this;
    }

    public function is_callback_query()
    {
        return $this->callback_query;
    }

    public function setChatID(int $id): self
    {
        $this->chat_id = $id;

        return $this;
    }
}
