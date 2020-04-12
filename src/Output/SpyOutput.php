<?php

declare(strict_types=1);

namespace Chiron\Console\Output;

use Symfony\Component\Console\Output\Output;

//https://github.com/viserio/console/blob/master/Output/SpyOutput.php
//https://github.com/mnapoli/silly-pimple/blob/1e502d282c35843297ab17a1f016f0df18bcb677/tests/Mock/SpyOutput.php
//https://github.com/yuya-takeyama/console-output-spyoutput/blob/develop/src/SymfonyX/Component/Console/Output/SpyOutput.php
//https://github.com/yuya-takeyama/console-output-spyoutput/blob/c51f8e7f67a2bc6733aff5b71bc8c55e9eef38e3/tests/SymfonyX/Tests/Component/Console/Output/SpyOutputTest.php



//https://github.com/viserio/console/blob/9eb2715e7621e021652d2b6949ac301ee0dc866e/Tests/ApplicationTest.php#L936
//https://github.com/mnapoli/silly/blob/6937bed1f402adbb66a47aefde94df8599ef9e4b/tests/FunctionalTest.php#L427
//https://github.com/mnapoli/silly-php-di/blob/bd4af1c2cdf6141dc941a9a4728d1db4d4b01ef0/tests/ApplicationTest.php#L58

final class SpyOutput extends Output
{
    /**
     * Get the outputted string.
     *
     * @var string
     */
    public $output = '';

    /**
     * Writes a message to the output.
     */
    protected function doWrite(string $message, bool $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
