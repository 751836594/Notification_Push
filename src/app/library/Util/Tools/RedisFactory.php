<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 18/07/24
 * Time: 10:24
 */

namespace Util\Tools;

class RedisFactory
{

    /**
     * @param $config
     * @return \Redis
     */
    public static function create($config)
    {

        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect($config['host'], $config['port'], $config['timeout']);
        //密码设置
        if (isset($config['password'])) {
            $redis->auth($config['password']);
        }
        //数据库选择
        if (isset($config['database'])) {
            $redis->select($config['database']);
        }
        return $redis;
    }

    /**
     * @param $config
     * @return mixed
     */
    public static function get($config)
    {
        static $cache = [];
        $name = $config['host'] . ':' . $config['port'];
        //数据库选择
        if (isset($config['database'])) {
            $name .= ':' . $config['database'];
        }

        if (!isset($cache[$name])) {

            $cache[$name] = self::create($config);

        }
        return $cache[$name];

    }
}