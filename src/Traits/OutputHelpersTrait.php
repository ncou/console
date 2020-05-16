<?php

declare(strict_types=1);

namespace Chiron\Console\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

//use Symfony\Component\Console\Output\ConsoleOutputInterface;
//use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Trait expect command to set $output scope.
 */
// TODO : passer toutes les méthodes public en protected !!!!
trait OutputHelpersTrait
{
    //Colors alias used by symfony console can be : 'black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'
    // Options can be : bold, underscore, blink, reverse, conceal
    // TODO : ajoutr un style critical ou panic ou alert ou emergency qui reprendrait le style de error mais avec un style blink en plus ?
    protected static $styles = [
        'notice'    => 'fg=magenta',
        'warning'   => 'fg=black;bg=yellow;options=bold',
        'caution'   => 'fg=white;bg=red',
        'error'     => 'fg=white;bg=red;options=bold',
        'info'      => 'fg=green',
        'comment'   => 'fg=yellow',
        'success'   => 'fg=black;bg=green',
        'message'   => 'fg=black;bg=cyan',
        'default'   => 'fg=default;bg=default'
    ];

    /**
     * Write a string as informational output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as caution output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function caution($string, $verbosity = null)
    {
        $this->line($string, 'caution', $verbosity);
    }

    /**
     * Write a string as notice output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function notice($string, $verbosity = null)
    {
        $this->line($string, 'notice', $verbosity);
    }

    /**
     * Write a string as success output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function success($string, $verbosity = null)
    {
        $this->line($string, 'success', $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as message output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function message($string, $verbosity = null)
    {
        $this->line($string, 'message', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    protected function warning($string, $verbosity = null)
    {
        $this->line($string, 'warning', $verbosity);
    }


    /**
     * Write a string as standard output.
     *
     * @param string     $string
     * @param string     $style          The output style of the string
     */
    protected function line(string $string, string $style = 'default'): void
    {
        // override the style using predefined presets code/color if found.
        $style = isset(self::$styles[$style]) ? self::$styles[$style] : $style;

        $styledString = sprintf('<%s>%s</>', $style, $string);
        $this->output->writeln($styledString);
    }

    // TODO : il faudrait que les fonctions d'écriture (listing, info, text, write...etc) retournent "self" pour permettre de chainer les commandes du genre '$this->hr()->newLine(2);'
    protected function newLine(int $count = 1)
    {
        $this->output->newLine($count);
    }


    protected function listing(array $elements)
    {
        $elements = array_map(function ($element) {
            return sprintf(' • %s', $element);
        }, $elements);

        $this->writeln($elements);
    }

    protected function text($message)
    {
        $messages = is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(sprintf(' %s', $message));
        }
    }


    protected function text2($message, string $style = 'default')
    {
        $messages = is_array($message) ? array_values($message) : [$message];

        //$this->output->writeln('<comment>');
        foreach ($messages as $message) {
            $this->output->writeln('<comment>'.$message.'</>');
        }
        //$this->output->writeln('</>');
    }







    /**
     * Identical to write function but provides ability to format message. Does not add new line.
     *
     * @param string $format
     * @param array  ...$args
     */
    protected function sprintf(string $format, ...$args)
    {
        return $this->output->write(sprintf($format, ...$args), false);
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write($messages, bool $newline = false)
    {
        return $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function writeln($messages)
    {
        return $this->output->writeln($messages);
    }

    /**
     * Table helper instance with configured header and pre-defined set of rows.
     *
     * @param array  $headers
     * @param array  $rows
     * @param string $style could be : default / box / compact / borderless / box-double
     *
     * @return Table
     */
    protected function table(array $headers, array $rows = [], string $style = 'default'): Table
    {
        $table = new Table($this->output);

        return $table->setHeaders($headers)->setRows($rows)->setStyle($style);
    }


    /**
     * Format input to textual table.
     *
     * @param array                                     $headers
     * @param array|\Viserio\Contract\Support\Arrayable $rows
     * @param string                                    $style
     * @param array                                     $columnStyles
     *
     * @return void
     */
    /*
    protected function table2(array $headers, $rows, string $style = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }*/

    /**
     * Check if verbosity level of output is higher or equal to VERBOSITY_VERBOSE.
     *
     * @return bool
     */
    protected function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }
   
}
