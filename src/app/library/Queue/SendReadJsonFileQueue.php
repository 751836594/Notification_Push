<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/8/23
 * Time: 下午2:20
 */

namespace Queue;


use Util\Tools\RabbitQueue;

class SendReadJsonFileQueue
{
    public $queue;

    /**
     * @param $config
     * @return SendReadJsonFileQueue
     */
    public static function instance($config)
    {
        static $obj;
        if (!$obj) {
            $obj = new self($config);
        }

        return $obj;
    }

    /**
     * AddAllotFollowLogDateQueue constructor.
     * @param $config
     */
    public function __construct($config)
    {

        $this->queue = new RabbitQueue($config, 'send_read_json_file_queue');;
    }


    /**
     * 发送队列
     * @param $row
     */
    public function send($row)
    {
        $this->queue->put($row);
    }
}