<?php

$pharFile = __DIR__ . '/autotest.phar';

if (file_exists($pharFile))
    unlink($pharFile);

$phar = new Phar($pharFile, 0, 'autotest.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);

$phar->startBuffering();

$phar->addFromString('lib/Autotest/Autotest.php', file_get_contents('lib/Autotest/Autotest.php'));
$phar->addFromString('lib/Autotest/Factory.php', file_get_contents('lib/Autotest/Factory.php'));
$phar->addFromString('lib/Autotest/PHPUnitAutotest.php', file_get_contents('lib/Autotest/PHPUnitAutotest.php'));
$phar->addFromString('lib/Autotest/PHPSpecAutotest.php', file_get_contents('lib/Autotest/PHPSpecAutotest.php'));
$phar->addFromString('lib/Autotest/BehatAutotest.php', file_get_contents('lib/Autotest/BehatAutotest.php'));

$phar->setStub(getStub());

$phar->stopBuffering();

exec("chmod +x {$pharFile}");

function getStub() {
    return <<<'EOF'
#!/usr/bin/php
<?php
/*
 * This version of autotest.php is geared towards the usage
 * with phpspec (see phpspec.net)
 * The second step is to turn this script now php into
 * a Symfony2 Command
 */
    
Phar::mapPhar('autotest.phar');

require_once 'phar://autotest.phar/lib/Autotest/Factory.php';

checkArguments($argv);

$autotest = Autotest\Factory::create($argv[1], $argv[2]);
while (true) {
    $autotest->executeTest();
    while (!$autotest->canRetry()) {
        // we wait while prompting for retry key press
    }
}

function checkArguments($argv) {
    if (count($argv) != 3) {
        printUsage();
        die();
    }
}

function printUsage() {
    echo <<<EOT
   
Error: Wrong argument count

Usage: autotest <phpunit|phpspec|behat> <file>


EOT;
}

__HALT_COMPILER();
EOF;
}