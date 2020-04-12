<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Invoker\Invoker;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Terminal;

//https://github.com/spiral/console/blob/master/src/Command.php
//https://github.com/spiral/console/blob/master/src/Traits/HelpersTrait.php

//https://github.com/viserio/console/blob/master/Command/AbstractCommand.php
//https://github.com/leevels/console/blob/master/Command.php

//https://github.com/illuminate/console/blob/master/Command.php
//https://github.com/symfony/console/blob/master/Command/Command.php

//https://github.com/illuminate/console/blob/6.x/Concerns/InteractsWithIO.php


//Style des tableaux avec simple ou double bordure =>   https://github.com/symfony/console/blob/master/Helper/Table.php#L808


//https://github.com/symfony/console/blob/master/Style/SymfonyStyle.php#L100

// TESTS !!!!!!!!
//https://github.com/laravel/framework/blob/7.x/tests/Console/CommandTest.php


/**
 * Provides automatic command configuration and access to global container scope.
 */
// TODO : ajouter le containerAwareTrait + ContainerAwareInterface !!!!
// TODO : il faudrait pas ajouter une ligne du style "abstract function perform(): int" ????
abstract class AbstractCommand extends SymfonyCommand
{
    /**
     * The console command input.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The console command output.
     *
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $output;

    /** @var ContainerInterface */
    protected $container;

    //Colors alias used by symfony console can be : 'black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'
    protected static $styles = [
        'notice' => 'fg=magenta',
        'warning' => 'fg=black;bg=yellow',
        'caution' => 'fg=white;bg=red',
        'error' => 'fg=white;bg=red',
        'info' => 'fg=green',
        'comment' => 'fg=yellow',
        'success' => 'fg=black;bg=green',
        'question' => 'fg=black;bg=cyan',
        'default' => 'fg=default;bg=default'
    ];

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Get the output implementation.
     *
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     *
     * @codeCoverageIgnore
     */
    // TODO : méthode à renommer en "output()" ????
    // TODO : il faudrait pas que la valeur de retour soit directement un OutputInterface ????
    // TODO : vérifier l'utilité de cette méthode.
    public function getOutput(): SymfonyStyle
    {
        return $this->output;
    }

    /**
     * Store the input and output object, and 'Run' the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    // TODO : déplacer ce code dans la méthode execute !!!!
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        // TODO : créer une variable protected nommée "$this->io" qui contiendra le style, ne pas utiliser "$this->output"
        $this->output = new SymfonyStyle($input, $output);

        /*
        $this->output = new SymfonyStyle(
            $input,
            $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output
        );*/

        return parent::run($input, $output);
    }

    /**
     * {@inheritdoc}
     *
     * Pass execution to "perform" method using container to resolve method dependencies.
     */
    // TODO : lever une logicexception si la méthode 'perform' n'est pas trouvée dans la classe mére ?
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->container === null) {
            throw new LogicException('Your forgot to call the setContainer function.');
        }

        $invoker = new Invoker($this->container);

        // TODO : lever une exception si la méthode perform n'est pas présente !!!!
        // TODO : ajouter un contrôle sur la valeur de retour pour s'assurer que c'est bien un int qui est renvoyé ??? ou alors retourner d'office le code 0 qui indique qu'il n'y a pas eu d'erreurs ????
        return (int) $invoker->call([$this, 'perform']);
    }

    /**
     * Call another console command.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     */
    // TODO : méthode à tester
    public function call(string $command, array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, $this->getOutput());
    }

    /**
     * Call another console command silently.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     */
    // TODO : méthode à tester
    public function callSilent(string $command, array $arguments = []): int
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
    protected function runCommand($command, array $arguments, OutputInterface $output): int
    {
        $arguments['command'] = $command;

        $command = $this->resolveCommand($command);
        $input = $this->createInputFromArguments($arguments);

        return $command->run($input, $output);
    }

    // TODO : permettre de passer un nom de classe et utiliser le container pour instancier la classe de la commande associée. https://github.com/illuminate/console/blob/master/Command.php#L149
    // autre exemple ;   https://github.com/viserio/console/blob/master/Command/AbstractCommand.php#L219   +   https://github.com/viserio/console/blob/master/Application.php#L191
    protected function resolveCommand(string $command): SymfonyCommand
    {
        return $this->getApplication()->find($command);
    }

    /**
     * Create an input instance from the given arguments.
     *
     * @param  array  $arguments
     * @return ArrayInput
     */
    protected function createInputFromArguments(array $arguments): InputInterface
    {
        // propagate the existing parameters from the orignal context command.
        $arguments = array_merge($arguments, $this->getContext());

        $input = new ArrayInput($arguments);

        if ($input->hasParameterOption(['--no-interaction', '-n'], true)) {
            $input->setInteractive(false);
        }

        return $input;
    }

    protected function getContext(): array
    {
        $context = [];

        foreach ($this->input->getOptions() as $key => $value) {
            if (in_array($key, ['ansi','no-ansi','no-interaction','quiet','verbose']) && $value === true) {
                $context["--{$key}"] = $value;
            }
        }

        return $context;
    }


    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function error($string, $verbosity = null)
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
    public function warning($string, $verbosity = null)
    {
        $this->line($string, 'warning', $verbosity);
    }


    /**
     * Write a string as standard output.
     *
     * @param string     $string
     * @param string     $style          The output style of the string
     */
    public function line(string $string, string $style = 'default'): void
    {
        // override the style using predefined presets code/color if found.
        $style = isset(self::$styles[$style]) ? self::$styles[$style] : $style;

        $styledString = sprintf('<%s>%s</>', $style, $string);
        $this->getOutput()->writeln($styledString);
    }

    // TODO : il faudrait que les fonctions d'écriture (listing, info, text, write...etc) retournent "self" pour permettre de chainer les commandes du genre '$this->hr()->newLine(2);'
    public function newLine(int $count = 1)
    {
        $this->getOutput()->newLine($count);
    }


    public function listing(array $elements)
    {
        $elements = array_map(function ($element) {
            return sprintf(' • %s', $element);
        }, $elements);

        $this->writeln($elements);
    }

    public function text($message)
    {
        $messages = is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(sprintf(' %s', $message));
        }
    }


    public function text2($message, string $style = 'default')
    {
        $messages = is_array($message) ? array_values($message) : [$message];

        //$this->getOutput()->writeln('<comment>');
        foreach ($messages as $message) {
            $this->getOutput()->writeln('<comment>'.$message.'</>');
        }
        //$this->getOutput()->writeln('</>');
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
     * Outputs a series of minus characters to the standard output, acts as a visual separator.
     *
     * @param bool $newline Whether to add a newline
     * @return void
     */
    // TODO : permettre de passer le séparateur en paramétre (on peut imaginer un séparateur '*' ou '-' ou '~' ou '#' ou '_' etc...)
    public function hr(bool $newline = false): void
    {
        // TODO : remonter la création du terminal dans le constructeur et l'ajouter en variable protected de cette classe abstraite.
        $terminal = new Terminal();
        $width = $terminal->getWidth();

        $this->write('', $newline);
        $this->write(str_repeat('-', $width));
        $this->write('', $newline);
    }





/*

    public function confirm($question, $defaults = false): bool
    {
        return $this->output->confirm($question, $defaults);
    }
*/



    /**
     * Configures the command.
     */
    /*
    protected function configure(): void
    {
        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);

        foreach ($this->defineOptions() as $option) {
            call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            call_user_func_array([$this, 'addArgument'], $argument);
        }
    }*/

    /**
     * Define command options.
     *
     * @return array
     */
    /*
    protected function defineOptions(): array
    {
        return static::OPTIONS;
    }*/

    /**
     * Define command arguments.
     *
     * @return array
     */
    /*
    protected function defineArguments(): array
    {
        return static::ARGUMENTS;
    }*/



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
    public function table2(array $headers, $rows, string $style = 'default', array $columnStyles = []): void
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
     * Determine if the given argument is present.
     *
     * @param string|int $name
     *
     * @return bool
     */
    // TODO : je pense que cette fonction ne sert pas à grand chose !!!!
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
    // TODO : méthode à virer elle ne sert pas à grand chose !!!!
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








    /**
     *
     * @param string $message
     * @param string $format
     */
    // TODO : ajouter un write() [pas un writeln !!!!] pour afficher le texte directement !!!
    // TODO : à virer cela ne sert pas à grand chose.
    public function time(string $message, string $format = 'H:i:s'): string
    {
        return ($format ? sprintf('[%s] ', date($format)) : ' ').$message;
    }



    /**
     * Write a string in an alert box.
     *
     * @param string $string
     *
     * @return void
     */
    // TODO : renommer la méthode en frame() et permettre de passer en 2eme argument le style (star / cross / box / box-double).
    // TODO : permettre de passer un tableau de string en entrée de la méthode.
    // TODO : virer le new line de cette fonction, l'utilisateur le rajoutera manuellement !!!
    public function alert(string $string): void
    {
        $length = strlen(strip_tags($string)) + 8;

        $this->comment(str_repeat('*', $length));
        $this->comment('*   ' . $string . '   *');
        $this->comment(str_repeat('*', $length));

        $this->newLine();
    }

    public function alert2(string $string): void
    {
        $length = strlen(strip_tags($string)) + 8;

        $this->comment('┌'. str_repeat('─', $length-2). '┐');
        $this->comment('│   ' . $string . '   │');
        $this->comment('└' . str_repeat('─', $length-2). '┘');

        $this->newLine();
    }


    public function alert3(string $string): void
    {
        $length = strlen(strip_tags($string)) + 8;

        $this->comment('╔'. str_repeat('═', $length-2). '╗');
        $this->comment('║   ' . $string . '   ║');
        $this->comment('╚' . str_repeat('═', $length-2). '╝');

        $this->newLine();
    }

    public function alert4(string $string): void
    {
        $length = strlen(strip_tags($string)) + 8;

        // This piece of code allow to calculate correctly the length when the color tags are présents (ex for a color tag : "\033[2;35m")
        $length = \Symfony\Component\Console\Helper\Helper::strlenWithoutDecoration($this->output->getFormatter(), $string) + 8;

        //die(var_dump($length));


        $this->comment('+'. str_repeat('-', $length-2). '+');
        $this->comment('│   ' . $string . '   │');
        $this->comment('+' . str_repeat('-', $length-2). '+');

        $this->newLine();
    }

    public function alert5(string $string): void
    {
        // This piece of code allow to calculate correctly the length when the color tags are présents (ex for a color tag : "\033[2;35m")
        $length = \Symfony\Component\Console\Helper\Helper::strlenWithoutDecoration($this->output->getFormatter(), $string) + 8;



        $message[] = '+'. str_repeat('-', $length-2). '+';
        $message[] = '│   ' . $string . '   │';
        $message[] = '+' . str_repeat('-', $length-2). '+';

        $this->text2($message, 'comment');

        $this->newLine();
    }

    /**
     * Write a string as task output.
     *
     * @param string   $string
     * @param callable $callable
     *
     * @return void
     */
    // TODO : à conserver ??? cela ne semble pas trés utile !!!
    public function task($string, callable $callable): void
    {
        $result = $callable() ? '<info>✔</info>' : '<error>fail</error>';

        $this->line($string . ' : ' . $result);
    }

}
