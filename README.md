## Installation

You can install the package via composer:

```bash
composer require lex19/telegram
```

Then you need publish config and create main Reception which will receive and process requests from the telegram hook

```bash
php artisan telegram:install
```

If you want to receive messages via a telegram hook, you need to create a route for this

```php
// routes/web.php

use lex19\Telegram\Facades\Telegram;
use App\Telegram\Receptions\MainReception;

Route::any('hook', function(){
    return Telegram::hook(MainReception::class);
});

```

To install the hook (setWebHook), you just need to make a GET request for this route from the domain on which the bot will run.

## Notice

127.0.0.1 or localhost is not suitable. For local development, you can use the [ngrok](https://ngrok.com/) , and install a hook via a GET request to the address given to you:
https://sample-address-from-ngrok.ngrok.io/hook

## Usage

If you want just send message by telegram_id you can do this:

```php
// AnyClass.php

use lex19\Telegram\Facades\Telegram;

Telegram::send([
    "chat_id" => 123456,
    "text" => "hello stranger!"
]);

```

To reply to messages from the bot, you need to create a command

```bash
php artisan telegram:command CommandName
```

This will create the command App\Telegram\Commands\CommandName

```php
// app/Telegram/Commands/CommandName.php

namespace App\Telegram\Commands;

use lex19\Telegram\Command;

class CommandName extends Command
{

  public function handle()
  {
    return $this->telegram->send('hello');
  }

}
```

And after that you can define this command in Reception in the commands property:

```php
// app/Telegram/Receptions/MainReception.php

namespace App\Telegram\Receptions;

use lex19\Telegram\BaseReception;

class MainReception extends BaseReception
{

  protected $commands = [
    'say hello' => \App\Telegram\Commands\CommandName::class
  ];

}

```

You can bind commands to requests in different ways:

```php
// app/Telegram/Receptions/MainReception.php

namespace App\Telegram\Receptions;

use lex19\Telegram\BaseReception;
use App\Telegram\Commands\CommandName;

class MainReception extends BaseReception
{

  protected $commands = [
    'say hello' => CommandName::class,
    'say hello myFunc' => [CommandName::class, 'myFunc'], // you use not just 'handle' method
  ];


    public function init()
    {
        $this->add('dynamic hello', [CommandName::class, 'dynamicHello']);
        $this->add([
            'dynamic hello 1' => [CommandName::class, 'dynamicHello1'],
            'dynamic hello 2' => [CommandName::class, 'dynamicHello2'],
        ]);
        $command = new CommandName;
        $this->add('dynamic hello 3', [$command, 'dynamicHello3']);
    }

}

```

If you want the following message, regardless of its content, to run a specific command, you can define it inside the current command:

```php
// app/Telegram/Commands/CommandName.php

namespace App\Telegram\Commands;

use lex19\Telegram\Command;

class CommandName extends Command
{

  public function handle()
  {
    $this->setNext([CommandName::class, 'next']); // any next message will trigger this command

    return $this->telegram->send('hello');
  }


  public function next()
  {
    $text = $this->telegram->text;
    return $this->telegram->send("I don't care what you wrote there, but here it is: $text");
  }

}


```
