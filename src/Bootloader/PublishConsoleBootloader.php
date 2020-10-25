<?php

declare(strict_types=1);

namespace Chiron\Console\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\PublishableCollection;

final class PublishConsoleBootloader extends AbstractBootloader
{
    public function boot(PublishableCollection $publishable, Directories $directories): void
    {
        $publishable->add(__DIR__ . '/../../config/console.php.dist', $directories->get('@config/console.php'));
    }
}
