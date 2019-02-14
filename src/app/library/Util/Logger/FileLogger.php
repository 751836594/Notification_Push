<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/12/12
 * Time: 11:59 AM
 */

namespace Util\Logger;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;


class FileLogger
{
    private $logger;

    /**
     * @param string $path
     * @return FileLogger
     */
    public static function instance($path = __DIR__.'/../../app.log')
    {
        return new self($path);

    }

    public function __construct($path)
    {

        $this->logger = new FileAdapter($path);
    }

    public function debug($message)
    {
        $this->logger->log(json_encode($message), Logger::DEBUG);
    }


}