<?php
/**
 * 服务的配置文件加载器
 * User: steven
 * Date: 18-07-06
 * Time: 09:51
 */


namespace Util\Common;

use Util\Tools\ArrayHelper;
use Phalcon\Config\Adapter\Ini as EnvConfig;
class Config
{

    /**
     * 加载通用config
     * @return \Phalcon\Config
     */
    private static function loadCommon()
    {
        static $config;
        if ($config) {
            return $config;
        }
        $file = self::loadMode();
        $config = include $file;
        return $config->toArray();
    }


    /**
     * 加载错误码文件
     * @return mixed
     */
    public static function loadError()
    {
        static $error;
        if ($error) {
            return $error;
        }
        $file = APP_PATH . '/config/error.php';
        $error = include $file;
        return $error;
    }


    /**
     * 加载所有的白名单配置
     * @return mixed
     */
    public static function loadWhiteList()
    {
        static $error;
        if ($error) {
            return $error;
        }
        $file = APP_PATH . '/config/white_list.php';
        $error = include $file;
        return $error;
    }


    /**
     * 加载项目config
     * @return mixed
     */
    private static function loadPrivate()
    {
        static $config;
        if ($config) {
            return $config;
        }
        $file = APP_PATH . '/config/config.php';
        $config = include $file;
        return $config->toArray();
    }


    /**
     * 获取config配置
     * @param $name
     * @return \Phalcon\Config|string
     */
    public static function get($name)
    {
        $config = new EnvConfig(BASE_PATH.'/.env');
        $environment = $config->development->environment;
        $baseConfig = include APP_PATH . "/config/config".(empty($environment) ? '' : '_'.$environment).".php";
        $params = ($config->merge($baseConfig))->toArray();
        return  isset($params[$name]) ? $params[$name] : '';
    }

    public static function loadConfig()
    {
        static $config;
        if (empty($config)) {
            $commonConfig = self::loadCommon();
            $loadConfig = self::loadPrivate();
            $config = ArrayHelper::arrayMerge($commonConfig, $loadConfig);
        }

        return $config;
    }


    /**
     * 获取通用errMsg
     * @param $code
     * @return string
     */
    public static function getErrorMsg($code)
    {
        static $error;

        if (empty($error)) {
            $error = self::loadError();
        }

        return isset($error[$code]) ? $error[$code] : '';
    }


    /**
     * 获取通用errMsg
     * @param $name
     * @return string
     */
    public static function getWhiteListInfo($name)
    {
        static $list;

        if (empty($list)) {
            $list = self::loadWhiteList();
        }

        return isset($list[$name]) ? $list[$name] : '';
    }

    /**
     * 加载当前环境
     * @return mixed
     */
    public static function loadMode()
    {
        static $configFile;
        if ($configFile) {
            return $configFile;
        }
        $file = BASE_PATH . '/api/.env';
        $mode = trim(@file_get_contents($file));
        switch ($mode){
            case 'development':
                $configFile = APP_PATH . '/config/config_development.php';
                break;
            case 'production':
                $configFile = APP_PATH . '/config/config_production.php';
                break;
            case 'staging':
                $configFile = APP_PATH . '/config/config_staging.php';
                break;
            default:
                $configFile = APP_PATH . '/config/config_testing.php';
                break;
        }
        return $configFile;
    }







}

