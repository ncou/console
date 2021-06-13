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

//https://github.com/tarlepp/symfony-flex-backend/blob/fd64f2e263517724f30a2c91154e7d8dd75d58d7/src/Command/HelperConfigure.php

// TODO : exemple avec un parser pour la signature de la commande. exemple : protected $signature = 'view:publish {--f|force}';
//https://github.com/hyperf/command/tree/master/src
//https://github.com/illuminate/console/blob/ba26417c3e34b7f733269778433bd005de8cdbb3/Command.php#L96
//https://github.com/mnapoli/silly/blob/81d93cde868c25f1b925349400c7d88a26a37cef/src/Application.php#L203

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
