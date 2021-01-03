<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Container\SingletonInterface;

use Symfony\Component\Console\Command\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

use Throwable;

// TODO : utiliser la fonction configureIO pour récupérer les paramétres style '--no-ansi' ou '--ansi' et donc avoir un output correctement configuré :
//https://github.com/spiral/framework/blob/master/src/Console/src/Console.php#L103
//https://github.com/symfony/console/blob/5.x/Application.php#L880

// Exemple avec Tracy : https://github.com/Kdyby/Console/blob/master/src/Application.php

// Exemple pour charger les commandes présentes dans un répertoire : https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Console/Kernel.php#L208
// Autre Exemple pour charger des commandes : https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Console/Application.php#L160

// TODO : passer la classe en final et les propriétés protected en private !!!!!
class Console implements SingletonInterface
{
    private $container;
    /** SymfonyApplication */
    private $application;
    /** CommandLoaderInterface */
    private $commandLoader;

    public function __construct(CommandLoaderInterface $commandLoader)
    {
        $this->commandLoader = $commandLoader;
    }

    /**
     * Gets the name of the application.
     *
     * @return string The application name
     */
    public function getName(): string
    {
        return $this->getApplication()->getName();
    }

    /**
     * Sets the application name.
     **/
    public function setName(string $name): void
    {
        $this->getApplication()->setName($name);
    }

    /**
     * Gets the application version.
     *
     * @return string The application version
     */
    public function getVersion(): string
    {
        return $this->getApplication()->getVersion();
    }

    /**
     * Sets the application version.
     */
    public function setVersion(string $version): void
    {
        $this->getApplication()->setVersion($version);
    }

    /**
     * Returns the long version of the application.
     *
     * @return string The long application version
     */
    public function getLongVersion(): string
    {
        return $this->getApplication()->getLongVersion();
    }

    /**
     * Returns true if the command exists, false otherwise.
     *
     * @return bool true if the command exists, false otherwise
     */
    public function has(string $name): bool
    {
        return $this->getApplication()->has($name);
    }

    /**
     * Run console application (Proxy method).
     *
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Throwable When running fails.
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $input = $input ?? new ArgvInput();
        $output = $output ?? new ConsoleOutput();

        return $this->getApplication()->run($input, $output);

        // TODO : exemple de code pour gérer le retour vide ou null ou suppérieur à 255 (qui est la limite, et normalement ce code est réservé à PHP)
        // https://github.com/cakephp/console/blob/4.x/CommandRunner.php#L174
        /*
        if ($result === null || $result === true) {
            return CommandInterface::CODE_SUCCESS;
        }
        if (is_int($result) && $result >= 0 && $result <= 255) {
            return $result;
        }

        return CommandInterface::CODE_ERROR;
        */
    }

    /**
     * Registers a new command (Proxy method).
     *
     * @return Command The newly created command
     */
    // TODO : c'est quoi l'utilité de faire un register ? Car la classe Command toute seule ne fonctionnera pas car on doit surcharger a méthode execute() pour implémenter la logique de la commande !!!!
    /*
    public function register(string $name): Command
    {
        return $this->getApplication()->add(new Command($name));
    }*/

    /**
     * Adds an array of command objects (Proxy method).
     *
     * If a Command is not enabled it will not be added.
     *
     * @param Command[] $commands An array of commands
     */
    public function addCommands(array $commands): void
    {
        foreach ($commands as $command) {
            $this->getApplication()->add($command);
        }
    }

    /**
     * Adds a command object (Proxy method).
     *
     * If a command with the same name already exists, it will be overridden.
     * If the command is not enabled it will not be added.
     *
     * @return Command|null The registered command if enabled or null
     */
    public function add(Command $command): ?Command
    {
        return $this->getApplication()->add($command);
    }

    /**
     * Finds a command by name or alias.
     *
     * Contrary to get, this command tries to find the best
     * match if you give it an abbreviation of a name or alias.
     *
     * @return Command A Command instance
     *
     * @throws CommandNotFoundException When command name is incorrect or ambiguous
     */
    public function find(string $name): Command
    {
        return $this->getApplication()->find($name);
    }

    // TODO : créer des méthodes proxy pour rendre les méthode parent "add()" ou "register()" visible depuis cette classe Console::class
    public function addCommand(string $name, string $command): void
    {
        // TODO : passer en paramétre à la méthode addCommand() seulement un string $className et détecter dans ce bout de code le name/command pour ensuite le passer à la méthode set(). Si le defaultName() n'est pas défini, alors lever une CommandException avec les message d'erreur qui va bien. + Faire un try/catch dans la classe ConsoleBootloader pour transformer cette exception en ApplicationException (éventuellement, sinon on pourrait laisser l'erreru se propager naturellement !!!!)
        $this->commandLoader->set($name, $command);
    }

    // TODO : méthode à virer !!!
    // TODO : lever une consoleException si la command n'est pas trouvée, je pense qu'il faut catcher les ContainerException et les convertir en ConsoleException !!!!
    public function addCommand_OLD(string $className): void
    {
        $command = $this->container->get($className);

        // TODO : Faire un test si la classe à un ContainerAwareInterface dans ce cas on injecte le container.
        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->container);
        }

        $this->getApplication()->add($command);
    }

    // TODO : méthode à virer.
    public function setCommandLoader_OLD(CommandLoaderInterface $commandLoader): void
    {
        $this->getApplication()->setCommandLoader($commandLoader);
    }




    public function getApplication(): SymfonyApplication
    {
        if ($this->application !== null) {
            return $this->application;
        }

        // TODO : configurer le nom et la version de l'Application Console Symfony
        $this->application = new SymfonyApplication();

        //$console->setName('TODO');
        //$console->setVersion('TODO');

        $this->application->setCatchExceptions(false);
        $this->application->setAutoExit(false);

        $this->application->setCommandLoader($this->commandLoader);

        return $this->application;
    }
}
