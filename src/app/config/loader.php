<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir,
        $config->application->libraryDir,
        $config->application->serviceDir,
    ]
);

$loader->registerNamespaces([
    'Service' => $config->application->serviceDir,
]);

$loader->register();
