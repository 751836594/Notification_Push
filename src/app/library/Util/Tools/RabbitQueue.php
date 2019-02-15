<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/7/19
 * Time: 上午9:42
 */

namespace Util\Tools;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitQueue
{

    /**
     * @var AMQPStreamConnection
     */
    protected $connection;
    protected $queue_key;
    protected $exchange_key;
    protected $exchange_suffix;
    protected $priority;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * RabbitQueue constructor.
     * @param $config
     * @param $queue_name
     * @param null $priority
     */
    public function __construct($config, $queue_name,$priority=null)
    {
        $this->connection = new AMQPStreamConnection($config['host'], $config['port'], $config['user'], $config['pass'],$config['vhost']);
        $this->queue_key = $queue_name;
        $this->exchange_suffix = $config['exchange'];
        $this->priority=$priority;
        $this->channel = $this->connection->channel();

        $this->bind_exchange();
        return $this->connection;
    }

    /**
     * 绑定交换机
     * @return mixed|null
     */
    protected function bind_exchange() {
        $queue_key=$this->queue_key;
        $exchange_key = $this->exchange_suffix;
        $this->exchange_key = $exchange_key;
        $channel = $this->channel;

        if(!empty($this->priority)){
            $priorityArr = array('x-max-priority' => array('I', $this->priority));
            $size = $channel->queue_declare($queue_key, false, true, false, false,false,$priorityArr);
        }else{
            $size = $channel->queue_declare($queue_key, false, true, false, false);
        }
        $channel->exchange_declare($exchange_key, 'topic', false, true, false);
        $channel->queue_bind($queue_key, $exchange_key,$queue_key);
        $this->channel=$channel;
        return $size ;
    }


    /**
     * 发送数据到队列
     * @param $jobs = array('key'=>'val')
     */
    public function put($jobs)
    {
        $channel = $this->channel;
        $value = json_encode($jobs);
        $toSend = new AMQPMessage($value, array('content_type' => 'application/json', 'delivery_mode' => 2));
        $channel->basic_publish($toSend, $this->exchange_key,$this->queue_key);
    }

    /**
     * 获取数据
     * @return mixed
     */
    public function get()
    {
        $channel = $this->channel;
        $message = $channel->basic_get($this->queue_key);
        if (!$message) {
            return  array(null,null);
        }
        $ack = function() use ($channel,$message) {
            $channel->basic_ack($message->delivery_info['delivery_tag']);
        };
        $result = json_decode($message->body,true);
        return array($ack,$result);
    }

    /**
     * 关闭链接
     */
    public function close() {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * 获得队列长度
     * @return int
     */
    public function length(){
        $info =  $this->bind_exchange();
        return $info[1];
    }
}