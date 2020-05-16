<?php

declare(strict_types=1);

namespace Chiron\Console\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

//use Symfony\Component\Console\Output\ConsoleOutputInterface;
//use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Trait expect command to set $input scope.
 */
// TODO : passer toutes les méthodes public en protected !!!!
trait InputHelpersTrait
{
    /**
     * Determine if the given argument is present.
     *
     * @param string|int $name
     *
     * @return bool
     */
    public function hasArgument($name)
    {
        // TODO : regarder si il ne faut pas faire comme pour les options et aller chercher si il y a bien une valeur saisie par l'utilisateur dans les cas d'arguments facultatifs !!!
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @param string|null $key
     *
     * @return string|array|null
     */
    public function argument(?string $key = null)
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments(): array
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    /**
     * Check if a command option is set.
     *
     * @param string $key
     * @param bool   $checkShortName
     *
     * @return bool
     */
    // TODO : méthode à renommer du style getRawOptionValue()
    /*
    public function hasOption(string $key, bool $checkShortName = true): bool
    {
        $hasOption = $this->input->hasParameterOption('--' . $key);

        if ($checkShortName && $hasOption === false) {
            $hasOption = $this->input->hasParameterOption('-' . $key[0]);
        }

        // TODO : attention on dirait qu'on est en train de rechercher un argument plutot qu'une option !!!!
        if ($hasOption === false) {
            $hasOption = $this->input->hasParameterOption($key);
        }

        return $hasOption;
    }*/


    /**
     * Get the value of a command option.
     *
     * @param string|null $key
     *
     * @return string|array|bool|null
     */
    public function option(?string $key = null)
    {
        if ($key === null) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all of the options passed to the command.
     *
     * @return array
     */
    public function options(): array
    {
        return $this->option();
    }

}
