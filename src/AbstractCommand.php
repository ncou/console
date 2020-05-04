<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Invoker\Invoker;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chiron\Console\Traits\InputHelpersTrait;
use Chiron\Console\Traits\OutputHelpersTrait;
use Chiron\Console\Traits\CallCommandTrait;

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
// TODO : on ne peut pas ajouter une fonction abstraite "perform" car le constructeur n'est pas le même selon la classe. Réfléchir cependant à mettre dans cette classe une fonction protected perform qui throw une exception, cela éviterai un check si la méthode existe. Mais voir si cela fonctionne quand la signature de perform définiée dans la classe mére est différente, on risque d'avoir le mêm probléme qu'avec la signature de fonction abstraite !!!
abstract class AbstractCommand extends SymfonyCommand
{
    use InputHelpersTrait, OutputHelpersTrait, CallCommandTrait;

    /**
     * OutputInterface is the interface implemented by all Output classes. Only exists when command are being executed.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * InputInterface is the interface implemented by all input classes. Only exists when command are being executed.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Store the input and output object, and 'Perform()' the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        // TODO : on devrait plutot faire une vérif sur instanceof ContainerInterface plutot que juste sur "null" pour confirmer que c'est bien un container qui est utilisé !!!
        // TODO : controle à virer une fois qu'on utilisera le trait ContainerAwareTrait car il y a une méthode getContainer (qui léve une exception si le container n'est pas bon) qui fait la même chose
        // TODO : il faudra mettre le getContainer, dans un try catch et convertir le ContainerException qui sera renvoyé en un CommandException avec un code du style : new CommandException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        if ($this->container === null) {
            // TODO : lever une CommandException dans le cas ou on n'a pas appeller la méthode setContainer !!!
            throw new LogicException('Your forgot to call the setContainer function.');
        }

        $invoker = new Invoker($this->container);

        // TODO : lever une exception si la méthode perform n'est pas présente !!!!
            // TODO : lever une logicexception si la méthode 'perform' n'est pas trouvée dans la classe mére ? (voir même une CommandException)
        // TODO : ajouter un contrôle sur la valeur de retour pour s'assurer que c'est bien un int qui est renvoyé ??? ou alors retourner d'office le code 0 qui indique qu'il n'y a pas eu d'erreurs ????
        // TODO : il faudrait surement faire un try/catch autour de la méthode call, car si la méthode perform n'existe pas une exception sera retournée. Une fois le catch fait il faudra renvoyer une new CommandException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        return (int) $invoker->call([$this, 'perform']);
    }
}
