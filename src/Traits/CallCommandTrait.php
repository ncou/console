<?php

declare(strict_types=1);

namespace Chiron\Console\Traits;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Trait expect command to set $output and $input scopes.
 */
trait CallCommandTrait
{
    /**
     * Call another console command.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     */
    protected function call(string $command, array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, $this->output);
    }

    /**
     * Call another console command silently.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     */
    protected function callSilent(string $command, array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, new NullOutput());
    }

    /**
     * Run the given the console command.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    private function runCommand($command, array $arguments, OutputInterface $output): int
    {
        $arguments['command'] = $command;

        $command = $this->resolveCommand($command);
        $input = $this->createInputFromArguments($arguments);

        return $command->run($input, $output);
    }

    // TODO : permettre de passer un nom de classe et utiliser le container pour instancier la classe de la commande associée. https://github.com/illuminate/console/blob/master/Command.php#L149
    // autre exemple ;   https://github.com/viserio/console/blob/master/Command/AbstractCommand.php#L219   +   https://github.com/viserio/console/blob/master/Application.php#L191
    private function resolveCommand(string $command): SymfonyCommand
    {
        return $this->getApplication()->find($command);
    }

    /**
     * Create an input instance from the given arguments.
     *
     * @param  array  $arguments
     * @return ArrayInput
     */
    private function createInputFromArguments(array $arguments): InputInterface
    {
        // propagate the existing parameters from the orignal context command.
        $arguments = array_merge($arguments, $this->getContext());

        $input = new ArrayInput($arguments);

        if ($input->hasParameterOption(['--no-interaction', '-n'], true)) {
            $input->setInteractive(false);
        }

        return $input;
    }

    private function getContext(): array
    {
        $context = [];

        foreach ($this->input->getOptions() as $key => $value) {
            if (in_array($key, ['ansi','no-ansi','no-interaction','quiet','verbose']) && $value === true) {
                $context["--{$key}"] = $value;
            }
        }

        return $context;
    }

   
}
