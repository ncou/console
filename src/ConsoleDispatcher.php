<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Console\Console;
use Chiron\Core\Dispatcher\AbstractDispatcher;
use Throwable;

// TODO : utiliser ce code pour afficher les exceptions dans la console : https://github.com/webmozart/console/blob/master/src/UI/Component/ExceptionTrace.php

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
// TODO : déplacer le dispatcher dans le projet "chiron/console" !!!
final class ConsoleDispatcher extends AbstractDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner/ReactPhp.
        return PHP_SAPI === 'cli' && env('RR') === null && env('REACT_PHP') === null && env('WORKER_MAN') === null;
    }

    /**
     * @param Console $console
     *
     * @return int
     */
    // TODO : il manque le input et ouput pour la console, histoire de pouvoir paramétrer ces valeurs par l'utilisateur (notamment pour les tests)
    protected function perform(Console $console): int
    {
        //throw new \RuntimeException("Error Processing Rêquest </info>toto</>", 1);
        /*
        throw new \RuntimeException(
        'Uncaught Symfony\Component\Console\Exception\InvalidArgumentException: Incorrectly nested style tag found. in D:\xampp\htdocs\nano5\vendor\symfony\console\Formatter\OutputFormatterStyleStack.php:76
  Stack trace:
  #0 D:\xampp\htdocs\nano5\vendor\symfony\console\Formatter\OutputFormatter.php(167): Symfony\Component\Console\Formatter\OutputFormatterStyleStack->pop(Object(Symfony\Component\Console\Formatter\OutputFormatterStyle))
  #1 D:\xampp\htdocs\nano5\vendor\symfony\console\Formatter\OutputFormatter.php(127): Symfony\Component\Console\Formatter\OutputFormatter->formatAndWrap(, 0)
  #2 D:\xampp\htdocs\nano5\vendor\symfony\console\Output\Output.php(157): Symfony\Component\Console\Formatter\OutputFormatter->format()
  #3 D:\xampp\htdocs\nano5\vendor\symfony\console\Output\Output.php(132): Symfony\Component\Console\Output\Output->write(Array, true, 1)
  #4 D:\xampp\htdocs\nano5\vendor\chiron\chiron\src\ErrorHandler\ConsoleRenderer.php(146): Symfony\Component\Console\Output\Output->w'
);*/

        try {
            return $console->run();
        } catch (Throwable $e) {
            // TODO : il faudrait plutot utiliser le RegisterErrorHandler::renderException($e) pour générer l'affichage de l'exception !!!! Mais attention car cela effectue un die(1), et donc cela va arréter l'application au lieu de retourner le code d'erreur 1.
            //$console->handleException($e);
            $this->handleException($e);

            // return the default error code.
            return 1; // TODO : il faudrait pas retourner un code d'erreur 255 ???? Eventuellement utiliser une constante avec la valeur 255 dans : ErrorHandler::EXIT_CODE
        }
    }

    // TODO : externaliser ou utiliser le ErrorHandler pour gérer l'affichage de l'erreur => https://github.com/filp/whoops/blob/96b540726286e4d8f64f68efe6b260c8b4a00d6d/src/Whoops/Handler/PlainTextHandler.php
    private function handleException(Throwable $exception): void
    {
        $message = $this->getExceptionOutput($exception);

        $previous = $exception->getPrevious();
        while ($previous) {
            $message .= "\n\nCaused by:\n" . $this->getExceptionOutput($previous) . "\n";
            $previous = $previous->getPrevious();
        }

        $message .= "\nStack trace:\n" . $exception->getTraceAsString() . "\n";


        //$stderr = new StreamOutput(fopen('php://stderr', 'w'));
        //$stderr->write($message);
        fwrite(STDERR, $message);
    }

    /**
     * Get the exception as plain text.
     * @param \Throwable $exception
     * @return string
     */
    private function getExceptionOutput(Throwable $exception): string
    {
        /*
        return sprintf(
            "%s: %s in file %s on line %d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );*/

        return sprintf(
            "%s: %s \nIn %s on line %d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

    }
}
