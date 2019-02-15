<?php
namespace tasks;

use Phalcon\Cli\Task;
use Util\Socket\WebSocket;

/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2019/2/15
 * Time: 10:09 AM
 */


class NotificationTask extends Task
{

    public function runAction()
    {
        $socket = new WebSocket();
        $socket->run();
    }
}