<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Injector\Injector;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chiron\Console\Traits\InputHelpersTrait;
use Chiron\Console\Traits\OutputHelpersTrait;
use Chiron\Console\Traits\CallCommandTrait;
use Closure;

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
 * Base command with a bunch of helpers (for input/output and execute sub-commands).
 */
// TODO : ajouter le containerAwareTrait + ContainerAwareInterface !!!!
// TODO : on ne peut pas ajouter une fonction abstraite "perform" car le constructeur n'est pas le même selon la classe. Réfléchir cependant à mettre dans cette classe une fonction protected perform qui throw une exception, cela éviterai un check si la méthode existe. Mais voir si cela fonctionne quand la signature de perform définiée dans la classe mére est différente, on risque d'avoir le mêm probléme qu'avec la signature de fonction abstraite !!!
class Command extends SymfonyCommand
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
}
