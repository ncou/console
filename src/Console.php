<?php

declare(strict_types=1);

namespace Chiron\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;

class Console
{
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

        $console = new SymfonyConsole();

        //$console->setCatchExceptions(false);
        $console->setAutoExit(false);

        return $console;
    }

    // TODO : permettre de passer 2 paramétres à cette fonction : un input et output, cela permettra aussi de se simplifier la vie lors des tests.
    public function run(): int
    {
        return $this->application->run();
    }

    public function addCommand(string $className): void
    {
        $command = $this->container->get($className);

        // TODO : Faire un test si la classe à un ContainerAwareInterface dans ce cas on injecte le container.
        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->container);
        }

        $this->application->add($command);
    }
}
