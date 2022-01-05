<?php

namespace lex19\Telegram;

use Illuminate\Support\ServiceProvider;
use lex19\Telegram\Console\CommandCommand;
use lex19\Telegram\Console\InstallCommand;
use lex19\Telegram\Console\KeyboardCommand;
use lex19\Telegram\Console\ReceptionCommand;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/telegram.php', 'telegram');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CommandCommand::class,
                KeyboardCommand::class,
                ReceptionCommand::class,
                InstallCommand::class,
            ]);
            $this->publishes([
                __DIR__.'/../config/telegram.php' => config_path('telegram.php'),
              ], 'config');
        }
    }
}
