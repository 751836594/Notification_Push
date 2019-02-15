<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
return new \Phalcon\Config([
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'logsDir'        => APP_PATH . '/logs/',
        'baseUri'        => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ],
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => '127.0.0.1',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => '',
        'charset'     => 'utf8',
    ],
    'factory_redis' => [
        'host' => '172.19.20.63',
        'prefix' => '',
        'port' => '6379',
        'auth' => 'DMS@NaT3',
        'index' => '10',
        'persistent' => false,
    ],
    'socket' => [
        'web_socket' => [
            'config' => [
                'task_worker_num' => 4,
                'buffer_output_size' => 2 * 1024 * 1024,//发送输出缓存区内存尺寸
                'heartbeat_check_interval' => 60,// 心跳检测秒数
                'ssl_cert_file'=> '/server.pem',
                'ssl_key_file' => '/server.key',
            ],
            'params' => [
                'connect' => [
                    'ip' => '0.0.0.0',
                    'host' => 'https://www.test.com',
                    'port' => 9555
                ]
            ],
            'socketKey' => '4e5abdad9584b8681145sdf123c2130'
        ],
    ],
]);
