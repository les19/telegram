<?php

declare(strict_types=1);

namespace lex19\Telegram\Console;

use Illuminate\Console\GeneratorCommand;

class KeyboardCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'telegram:keyboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new telegram keyboard class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Keyboard';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $current = dirname(__DIR__, 2);

        return realpath($current . DIRECTORY_SEPARATOR . 'stubs/keyboard.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Telegram\Keyboards';
    }
}
