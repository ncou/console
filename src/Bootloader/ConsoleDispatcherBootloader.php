<?php

declare(strict_types=1);

namespace Chiron\Console\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Console\ConsoleDispatcher;

final class ConsoleDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application, AppConfig $config): void
    {
        $application->addDispatcher(resolve(ConsoleDispatcher::class));
    }
}
