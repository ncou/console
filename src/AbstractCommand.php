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
 * Provides automatic command configuration and access to global container scope.
 */
// TODO : ajouter le containerAwareTrait + ContainerAwareInterface !!!!
// TODO : on ne peut pas ajouter une fonction abstraite "perform" car le constructeur n'est pas le même selon la classe. Réfléchir cependant à mettre dans cette classe une fonction protected perform qui throw une exception, cela éviterai un check si la méthode existe. Mais voir si cela fonctionne quand la signature de perform définiée dans la classe mére est différente, on risque d'avoir le mêm probléme qu'avec la signature de fonction abstraite !!!
abstract class AbstractCommand extends SymfonyCommand
{
    use InputHelpersTrait, OutputHelpersTrait, CallCommandTrait;

    // TODO : virer ces deux constantes une fois que le support à la version 4.x de symfony/console est droppé !!!! + modifier les signature des méthode getname et has dans la partie CommandLoader pour ajouter le typehint 'string' !!!!
    public const SUCCESS = 0;
    public const FAILURE = 1;
    // TODO : vérifier si on conserve cette constante.
    public const ERROR = 255;

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









    protected const TITI = 'FOOBAR';


    public static function getTITI()
    {
        $r = new \ReflectionClass(static::class);

        return $r->getConstant('TITI');
    }



    public static function getTITI_OLD()
    {
        $reflector = new \ReflectionClass(static::class);

        //$constants = $reflector->getConstants();
        //return $constants;

        if ($reflector->hasConstant('TITI')) {
            return $reflector->getConstant('TITI');
        } else {
            return 'zuttttt';
        }
        //var_dump($mainReflection->getConstant('name'));//Primary
    }

    /**
     * @return string|null The default command name or null when no default name is set
     */
    public static function getTOTO()
    {
        $class = static::class;
        $r = new \ReflectionProperty($class, 'TOTO'); // defaultName

        return $class === $r->class ? static::$TOTO : null;
    }








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

        $injector = new Injector($this->container);

        // TODO : lever une exception si la méthode perform n'est pas présente !!!!
            // TODO : lever une logicexception si la méthode 'perform' n'est pas trouvée dans la classe mére ? (voir même une CommandException)
        // TODO : ajouter un contrôle sur la valeur de retour pour s'assurer que c'est bien un int qui est renvoyé ??? ou alors retourner d'office le code 0 qui indique qu'il n'y a pas eu d'erreurs ????
        // TODO : il faudrait surement faire un try/catch autour de la méthode call, car si la méthode perform n'existe pas une exception sera retournée. Une fois le catch fait il faudra renvoyer une new CommandException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        return (int) $injector->call(Closure::fromCallable([$this, 'perform']));

        // TODO : exemple de code pour gérer le retour vide ou null ou suppérieur à 255 (qui est la limite, et normalement ce code est réservé à PHP)
        // https://github.com/cakephp/console/blob/4.x/CommandRunner.php#L174
        /*
        if ($result === null || $result === true) {
            return CommandInterface::CODE_SUCCESS; //0
        }
        if (is_int($result) && $result >= 0 && $result <= 255) {
            return $result;
        }

        return CommandInterface::CODE_ERROR; //1
        */
    }


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
}
