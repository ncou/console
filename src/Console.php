<?php

declare(strict_types=1);

namespace Chiron\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use Throwable;

class Console
{
    protected const COLORS = [
        'bg:red'     => Color::BG_RED,
        'bg:cyan'    => Color::BG_CYAN,
        'bg:magenta' => Color::BG_MAGENTA,
        'bg:white'   => Color::BG_WHITE,
        'white'      => Color::LIGHT_WHITE,
        'green'      => Color::GREEN,
        'black'      => Color::BLACK,
        'red'        => Color::RED,
        'yellow'     => Color::YELLOW,
        'reset'      => Color::RESET,
    ];


    private $input;
    private $output;

    private $container;

    private $application;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->application = $this->getSymfonyConsole();
    }

    public function getSymfonyConsole(): SymfonyConsole
    {
        /*
        if ($this->application !== null) {
            return $this->application;
        }*/

        // TODO : configurer le nom et la version de l'Application Console Symfony 
        $console = new SymfonyConsole();

        $console->setCatchExceptions(false);
        $console->setAutoExit(false);
        //$console->setName('TODO');
        //$console->setVersion('TODO');

        return $console;
    }

    /**
     * Gets whether to catch exceptions or not during commands execution.
     *
     * @return bool Whether to catch exceptions or not during commands execution
     */
    /*
    public function areExceptionsCaught()
    {
        return $this->catchExceptions;
    }*/

    /**
     * Sets whether to catch exceptions or not during commands execution.
     */
    /*
    public function setCatchExceptions(bool $boolean)
    {
        $this->catchExceptions = $boolean;
    }*/

    /**
     * Gets whether to automatically exit after a command execution or not.
     *
     * @return bool Whether to automatically exit after a command execution or not
     */
    /*
    public function isAutoExitEnabled()
    {
        return $this->autoExit;
    }*/

    /**
     * Sets whether to automatically exit after a command execution or not.
     */
    /*
    public function setAutoExit(bool $boolean)
    {
        $this->autoExit = $boolean;
    }*/

    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Exception When running fails. Bypass this when {@link setCatchExceptions()}.
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {

        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        //$this->configureIO($input, $output);

        $this->input = $input;
        $this->output = $output;

        // TODO : on doit pouvoir stocker le $output dans $this->output pour ensuite l'utiliser lorsqu'on va afficher les exceptions via une méthode "renderThrowable()" ou "handleException()"
        return $this->application->run($input, $output);
    }

    // TODO : lever une consoleException si la command n'est pas trouvée, je pense qu'il faut catcher les ContainerException et les convertir en ConsoleException !!!!
    public function addCommand(string $className): void
    {
        $command = $this->container->get($className);

        // TODO : Faire un test si la classe à un ContainerAwareInterface dans ce cas on injecte le container.
        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->container);
        }

        $this->application->add($command);
    }


    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        $this->application->renderThrowable($e, $output);
    }


    



    // TODO : faire renvoyer un code d'erreur standard (soit 1 soit 255) par cette fonction (typehint = int) ?
    public function handleException(Throwable $e)
    {
        if ($this->output instanceof ConsoleOutputInterface) {
            $this->renderException($e, $this->output->getErrorOutput());
        } else {
            $this->renderException($e, $this->output);
        }
    }

    private function renderException(Throwable $e, OutputInterface $output): void
    {
        $this->application->renderThrowable($e, $output);
    }

    private function renderException2(Throwable $e, OutputInterface $output): void
    {

        //return $this->application->renderThrowable($e, $output);



/*
        $result = '';
        $result .= $this->renderHeader('[' . get_class($e) . "]\n" . $e->getMessage(), 'bg:red,white');

        $result .= $this->format(
            "<yellow>in</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>\n",
            $e->getFile(),
            $e->getLine()
        );

        $result .= $this->renderTrace2($e);

        $output->writeln($result);
*/




        $class = get_class($e);
        $message = $e->getMessage();

        $this->render("<bg=red;options=bold> $class </>");
        $this->output->writeln('');
        $this->output->writeln("<fg=default;options=bold>  $message</>");

        $this->renderTrace($this->getStacktrace($e));







    }

    /**
     * Render title using outlining border.
     *
     * @param string $title Title.
     * @param string $style Formatting.
     * @param int    $padding
     * @return string
     */
    private function renderHeader(string $title, string $style, int $padding = 0): string
    {
        $result = '';

        // TODO : on peut aussi utiliser le code suivant pour spliter la chaine en tableau (vérifier quand même que la string n'est pas vide ('')   =>   preg_split('/\r?\n/', $title)
        $lines = explode("\n", str_replace("\r", '', $title));

        $length = 0;
        array_walk($lines, function ($v) use (&$length): void {
            $length = max($length, mb_strlen($v));
        });

        $length += $padding;

        foreach ($lines as $line) {
            $result .= $this->format(
                "<{$style}>%s%s%s</reset>\n",
                str_repeat(' ', $padding + 1),
                $line,
                str_repeat(' ', $length - mb_strlen($line) + 1)
            );
        }

        return $result;
    }



    /**
     * Format string and apply color formatting (if enabled).
     *
     * @param string $format
     * @param mixed  ...$args
     * @return string
     */
    private function format(string $format, ...$args): string
    {
        $format = preg_replace_callback('/(<([^>]+)>)/', function ($partial) {
            $style = '';
            foreach (explode(',', trim($partial[2], '/')) as $color) {
                if (isset(self::COLORS[$color])) {
                    $style .= self::COLORS[$color];
                }
            }

            return $style;
        }, $format);   

        return sprintf($format, ...$args);
    }

    /**
     * Renders an message into the console.
     *
     * @return $this
     */
    protected function render(string $message, bool $break = true)
    {
        if ($break) {
            $this->output->writeln('');
        }

        $this->output->writeln("  $message");
    }


    /**
     * Renders the trace of the exception.
     */
    private function renderTrace(array $frames)
    {
        $vendorFrames = 0;
        $userFrames   = 0;
        foreach ($frames as $i => $frame) {
            if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE && strpos($frame['file'], '/vendor/') !== false) {
                $vendorFrames++;
                continue;
            }

/*
            if ($userFrames > static::VERBOSITY_NORMAL_FRAMES && $this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
                break;
            }
*/

            $userFrames++;

            $file     = $this->getFileRelativePath($frame['file'] ?? 'n/a');
            $line     = $frame['line'] ?? 'n/a';
            $class    = empty($frame['class']) ? '' : $frame['class'] . '::';
            $function = $frame['function'];
            $args     = $this->formatArgs($frame['args']);
            $pos      = str_pad((string) ((int) $i + 1), 4, ' ');

            if ($vendorFrames > 0) {
                $this->output->write(
                    sprintf("\n      \e[2m+%s vendor frames \e[22m", $vendorFrames)
                );
                $vendorFrames = 0;
            }

            $this->render("<fg=yellow>$pos</><fg=default;options=bold>$file</>:<fg=default;options=bold>$line</>");
            $this->render("<fg=white>    $class$function($args)</>", false);
        }

        /* Let's consider add this later...
         * if ($vendorFrames > 0) {
         * $this->output->write(
         * sprintf("\n      \e[2m+%s vendor frames \e[22m\n", $vendorFrames)
         * );
         * $vendorFrames = 0;
         * }.
         */
    }

    private function formatArgs(array $arguments, bool $recursive = true): string
    {
        $result = [];

        foreach ($arguments as $argument) {
            switch (true) {
                case is_string($argument):
                    $result[] = '"' . $argument . '"';
                    break;
                case is_array($argument):
                    $associative = array_keys($argument) !== range(0, count($argument) - 1);
                    if ($recursive && $associative && count($argument) <= 5) {
                        $result[] = '[' . $this->formatArgs($argument, false) . ']';
                    }
                    break;
                case is_object($argument):
                    $class    = get_class($argument);
                    $result[] = "Object($class)";
                    break;
            }
        }

        return implode(', ', $result);
    }


    /**
     * Render exception call stack.
     *
     * @param \Throwable       $e
     * @return string
     */
    private function renderTrace2(\Throwable $e): string
    {
        $stacktrace = $this->getStacktrace($e);
        if (empty($stacktrace)) {
            return '';
        }

        $result = $this->format("\n<red>Exception Trace:</reset>\n");

        foreach ($stacktrace as $trace) {
            if (isset($trace['type']) && isset($trace['class'])) {
                $line = $this->format(
                    ' <white>%s%s%s()</reset>',
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            } else {
                $line = $this->format(
                    ' <white>%s()</reset>',
                    $trace['function']
                );
            }

            if (isset($trace['file'])) {
                $line .= $this->format(
                    ' <yellow>at</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>',
                    $trace['file'],
                    $trace['line']
                );
            } else {
                $line .= $this->format(
                    ' <yellow>at</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>',
                    'n/a',
                    'n/a'
                );
            }

            $result .= $line . "\n";
        }

        return $result;
    }

    /**
     * Returns the relative path of the given file path.
     */
    private function getFileRelativePath(string $filePath): string
    {
        $cwd = (string) getcwd();

        if (!empty($cwd)) {
            return str_replace("$cwd/", '', $filePath);
        }

        return $filePath;
    }

    /**
     * Normalized exception stacktrace.
     *
     * @param \Throwable $e
     * @return array
     */
    // TODO : autre exemple : https://github.com/rollbar/rollbar-php/blob/057c9ea98f4bebf4aca10caca282adab03952670/src/DataBuilder.php#L520
    protected function getStacktrace(\Throwable $e): array
    {
        $stacktrace = $e->getTrace();

        if (empty($stacktrace)) {
            return [];
        }

        // Add the Exception's file and line as the last frame of the trace.
        $header = [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] + $stacktrace[0];

        if ($stacktrace[0] != $header) {
            array_unshift($stacktrace, $header);
        }

        return $stacktrace;
    }


}
