<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI,
    Phalcon\CLI\Console as ConsoleApp;
use Predis\Client as RedisClient;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Config\Adapter\Ini as EnvConfig;

$di = new CliDI();
define('VERSION', '1.0.0');
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__)));

define('BASE_PATH', (dirname(__DIR__)));
define('APP_PATH', dirname(__DIR__) . '/app');
/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new \Phalcon\Loader();
$loader->registerDirs(
    [
        APPLICATION_PATH . '/tasks',
        APPLICATION_PATH . '/Models',
    ]
);
$loader->registerNamespaces(
    [
        'library' => __DIR__ . '/library',
        'Phalcon' => __DIR__ . '/library/Phalcon',
        'Util' => __DIR__ . '/library/Util',
        'PhpAmqpLib' => __DIR__ . '/library/PhpAmqpLib',
        'Monolog' => __DIR__ . '/library/Monolog',
        'Psr' => __DIR__ . '/library/Psr',
        'tasks' => __DIR__ . '/tasks',
    ]
);
$loader->register();

//Create a console application
$console = new ConsoleApp();
$console->setDI($di);

$di->setShared('config', function () {
    $config = new EnvConfig(BASE_PATH.'/.env');
    $environment = $config->development->environment;
    $baseConfig = include APP_PATH . "/config/config".(empty($environment) ? '' : '_'.$environment).".php";
    return $config->merge($baseConfig);
});


$di->set('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'charset' => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});


$di->setShared('redis', function () {
    $config = $this->getConfig();

    $redisConfig = $config->redis->toArray();

    try {
        $sentinels = ['tcp://' . $redisConfig['host'] . ':' . $redisConfig['port']];

        $options = [
            'parameters' => [
                'password' => $redisConfig['auth'],
                'database' => $redisConfig['index'],
            ],
        ];

        $client = new RedisClient($sentinels, $options);
        return $client;
    } catch (Exception $ex) {

    }
});

$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Process the console arguments
 */
$arguments = array();

foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = "tasks\\" . $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

// define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));

define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

define('TERMINAL', 'app');


try {
    $console->handle($arguments);

} catch (\Phalcon\Exception $e) {
    print_r($e->getMessage());
    exit(255);
} catch (\Exception $e) {
    print_r($e->getMessage());
    exit(255);
}