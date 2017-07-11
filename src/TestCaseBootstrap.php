<?php

/*
 * Bootstrap of the Ride system.
 * File should be placed in application/src
 */

$autoloader = __DIR__ . '/../../../../vendor/autoload.php';
$parameters = __DIR__ . '/../../../../application/config/parameters.php';

// include the Composer autoloader
if (file_exists($autoloader)) {
    include_once $autoloader;
}

// read the parameters
if (file_exists($parameters)) {
    include $parameters;
}

if (!isset($parameters) || !is_array($parameters)) {
    $parameters = array();
}

if (class_exists('\Symfony\Component\VarDumper\VarDumper')) {
    $cloner = new Symfony\Component\VarDumper\Cloner\VarCloner();
    $cloner->setMaxItems(50);
    $dumper = 'cli' === PHP_SAPI ? new Symfony\Component\VarDumper\Dumper\CliDumper() : new Symfony\Component\VarDumper\Dumper\HtmlDumper();
    $handler = function ($var) use ($cloner, $dumper) {
        $dumper->dump($cloner->cloneVar($var));
    };
    Symfony\Component\VarDumper\VarDumper::setHandler($handler);
}
