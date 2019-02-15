<?php

namespace Util\Socket;

use JsonSerializable;
use Util\Auth\Sign;
use Util\Tools\RedisFactory;
use Util\Common\Config;
use Util\Tools\RedisKeyMap;

/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/11/6
 * Time: 4:15 PM
 */
class WebSocket implements JsonSerializable
{
    public $server;
    public $redis;
    const TYPE_LIST = [
        'login' => 1, //登录
        'reconnect' => 2,//重连,
        'allot_alert' => 3,//发送提醒
    ];

    // 实现的抽象类方法，指定需要被序列化JSON的数据
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            if ($val !== null) {
                $data[$key] = $val;
            }
        }
        return $data;
    }

    /**
     * WebSocket constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $socket = Config::get('socket');
        $this->server = new \swoole_websocket_server($socket['web_socket']['params']['connect']['ip'],
            $socket['web_socket']['params']['connect']['port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);

        $config = empty($config) ? $socket['web_socket']['config'] : $config;
        $this->server->set($config);
        $this->redis = RedisFactory::get(Config::get('factory_redis'));

    }

    /**
     * 执行主进程
     */
    public function run()
    {
        $this->server->on('open', [$this, '_Open']);
        $this->server->on('receive', [$this, '_Receive']);
        $this->server->on('message', [$this, '_Message']);
        $this->server->on('close', [$this, '_Close']);
        $this->server->on('task', [$this, '_Task']);
        $this->server->on('finish', [$this, '_Finish']);
        $this->server->on('request', [$this, '_Request']);
        $this->server->on('shutdown', [$this, '_Shutdown']);
        $this->server->start();

    }


    public function send($message = '你有新的分配消息', $fd)
    {
        $this->_Push($this->server, $fd,
            ['type' => self::TYPE_LIST['allot_alert'], 'message' => $message]);
    }


    /**
     * 建立连接并完成握手后事件回调(当WebSocket客户端与服务器建立连接并完成握手后会回调此函数)
     * @param $server
     * @param $request
     */
    public function _Open($server, $request)
    {
        echo "fd:{$request->fd}已成功链接\n";
    }


    /**
     * @param $server
     * @param $fd
     * @param $reactor_id
     * @param $data
     */
    public function _Receive($server, $fd, $reactor_id, $data)
    {
        echo "1\n";
    }

    /**
     * 接受客户端数据事件(当服务器收到来自客户端的数据帧时会回调此函数)
     * @param $server
     * @param $frame
     */
    public function _Message($server, $frame)
    {
        $fd = $frame->fd;
        $data = json_decode($frame->data, true);
        $this->__disposeData($data, $fd, $server);
    }


    /**
     * 断开连接事件(当客户端关闭浏览器会回调此函数)
     * @param $server
     * @param $fd
     * @param $reactorId
     */
    public function _Close($server, $fd, $reactorId)
    {
        echo "fd:{$fd}主动断开\n";
        $this->__disposeData(['type' => 'close', 'reactor_id' => $reactorId], $fd, $server);
    }


    public function _Task($server, $task_id, $src_worker_id, $data)
    {
        $message = $this->__disposeTask($data, $server);
        return $message;
    }


    public function _Finish($server, $task_id, $message)
    {
        switch ($message['type']) {
            case "finish_login":
                echo $message['message'];
                break;
            case "finish_close":
                echo $message['message'];
                break;
            case "finish_finance_alert":
                echo $message['message'];
                break;
        }
    }


    /**
     * @param $request
     * @param $response
     * @return array
     */
    public function _Request($request, $response)
    {
        // 接收http请求从get获取message参数的值，给用户推送
        //$userList = $this->redis->HGETALL(RedisKeyMap::SOCKET_USER_LIST);
        $reqData = $request->get;

        $signStatus = Sign::verifySocketSign($reqData);
        if(!$signStatus['status']){
            return $response->end(json_encode([
                'status' => false,
                'message' => $signStatus['err_msg']
            ]));
        }
        /*if (empty($userList)) {
            return $response->end(json_encode([
                'status' => false,
                'message' => '无在线客户'
            ]));
        }
        if (!isset($userList[$reqData['uid']])) {
            return $response->end(json_encode([
                'status' => false,
                'message' => '无在线客户1'
            ]));
        }*/
        $uid = $reqData['uid'];
        $masterFd = $this->redis->HGET(RedisKeyMap::SOCKET_CONNECT_LIST, $uid);

        if (!empty($masterFd) && $this->server->exist($masterFd)) {
            $this->__disposeData($reqData, $masterFd, $this->server);
            return $response->end(json_encode([
                'status' => true,
                'message' => '发送成功'
            ]));
        } else {

            return $response->end(json_encode([
                'status' => false,
                'message' => '发送出问题'
            ]));
        }
    }


    /**
     * @param $ser
     * @param $fd
     */
    public function _Shutdown($ser, $fd)
    {
        foreach ($this->server->connections as $fd) {
            $this->server->push($fd, 'socket链接已关闭');
        }
        echo "链接已关闭\n";
    }

    /**
     * 处理ws的自定义信号
     * @param $data
     * @param $fd
     * @param $server
     * @return void
     */
    private function __disposeData($data, $fd, $server)
    {

        if (isset($data['type'])) {
            switch ($data['type']) {
                //处理登录状态
                case 'login':
                    $server->task([
                        'type' => 'login',
                        'message' => [
                            'fd' => $fd,
                            'uid' => $data['uid'],
                        ],
                    ]);
                    break;
                case 'close':
                    $server->task([
                        'type' => 'close',
                        'message' => [
                            'fd' => $fd,
                            'reactor_id' => $data['reactor_id']
                        ],
                    ]);
                    break;
                case 'send_finance_alert':
                    $server->task([
                        'type' => 'send_finance_alert',
                        'message' => [
                            'fd' => $fd,
                            'uid' => $data['uid'],
                            'message' => $data['message']
                        ],
                    ]);
                    break;
                case 'send_extension_alert' :
                    $server->task([
                        'type' => 'send_extension_alert',
                        'message' => [
                            'fd' => $fd,
                            'uid' => $data['uid'],
                            'message' => $data['message']
                        ],
                    ]);
                    break;
                default:
                    return;

            }
        }

    }


    /**
     * 处理task
     * @param $data
     * @param $server
     * @return string
     */
    private function __disposeTask($data, $server)
    {
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'login':
                    $message = $this->__setLogin($data['message'], $server);
                    break;
                case 'close':
                    $message = $this->__sendCloseEvent($data['message'], $server);
                    break;
                case 'send_finance_alert':
                    $message = $this->__sendFinanceAlertEvent($data['type'],$data['message'], $server);
                    break;
                case 'send_extension_alert':
                    $message = $this->__sendExtensionAlertEvent($data['type'],$data['message'], $server);
                    break;
                default:
                    $message = '无广播内容';
            }

            return $message;
        }
    }

    /**
     * 资产报件进度发送
     * @param $type
     * @param $message
     * @param $server
     * @return array
     */
    private function __sendFinanceAlertEvent($type,$message, $server)
    {
        $this->_Push($server,$message['fd'],['type' => $type , 'message' => $message['message']]);
        return ['type' => $type, 'message' => "uid:{$message['uid']},fd:{$message['fd']},message:{$message['message']}\n"];
    }

    /**
     * 续展提醒发送
     * @param $type
     * @param $message
     * @param $server
     * @return array
     */
    private function __sendExtensionAlertEvent($type,$message, $server)
    {
        $this->_Push($server,$message['fd'],['type' => $type , 'message' => $message['message']]);
        return ['type' => $type, 'message' => "uid:{$message['uid']},fd:{$message['fd']},message:{$message['message']}\n"];
    }


    /**
     * 设置login是的redis缓存
     * @param $message
     * @param $server
     * @return array
     */
    private function __setLogin($message, $server)
    {
        $this->redis->HSET(RedisKeyMap::SOCKET_CONNECT_LIST,$message['uid'], $message['fd']);

        return ['type' => 'finish_login', 'message' => "uid:{$message['uid']},fd:{$message['fd']}的连接接初始化完成\n"];
    }


    /**
     * close时更新缓存
     * @param $message
     * @param $server
     * @return array
     */
    private function __sendCloseEvent($message, $server)
    {
        $this->redis->HGET(RedisKeyMap::SOCKET_CONNECT_LIST, $message['fd']);
        return ['type' => 'finish_close', 'message' => "fd:{$message['fd']}的连接关闭完成\n"];
    }


    /**
     * @param $server
     * @param $fd
     * @param $data
     */
    public function _Push($server, $fd, $data)
    {
        $server->push($fd, json_encode($data));
    }


    /**
     * @param $server
     * @param $uid
     * @param $fd
     * @return array|mixed
     */
    private function _updateLoginUidFdList($server, $uid, $fd = 0)
    {
        $uidFdListJson = $this->redis->HGET(RedisKeyMap::SOCKET_CONNECT_LIST, $uid);
        $res = [];
        if (!empty($uidFdListJson)) {
            $uidFdListArr = json_decode($uidFdListJson, true);
        } else {
            $uidFdListArr = [];
        }


        if (!empty($fd)) {
            $uidFdListArr[] = $fd;
        }

        if (empty($uidFdListArr)) {
            $this->redis->HDEL(RedisKeyMap::SOCKET_USER_FD_LIST, $uid);
            $this->redis->HDEL(RedisKeyMap::SOCKET_USER_LIST, $uid);
            return [];
        }

        $uidFdListArr = array_unique($uidFdListArr);

        foreach ($uidFdListArr as $cacheFd) {
            if ($server->exist($cacheFd)) {
                $res[] = $cacheFd;
            }
        }

        if (empty($res)) {
            $this->redis->HDEL(RedisKeyMap::SOCKET_USER_FD_LIST, $uid);
            $this->redis->HDEL(RedisKeyMap::SOCKET_USER_LIST, $uid);
            return [];
        } else {
            $this->redis->HSET(RedisKeyMap::SOCKET_USER_FD_LIST, $uid, json_encode($res));
            return $res;
        }

    }


    /**
     * @param $server
     * @param $uid
     */
    private function _pushReLogin($server, $uid)
    {
        $uidFdListArr = $this->_updateLoginUidFdList($server, $uid);
        if (!empty($uidFdListArr)) {
            rsort($uidFdListArr);
            $pushFd = 0;
            foreach ($uidFdListArr as $fd) {
                if (!$server->exist($fd)) {
                    unset($uidFdListArr[$fd]);
                } else {
                    $pushFd = $fd;
                    break;
                }
            }
            if (!empty($pushFd)) {
                $this->_Push($server, $pushFd, ['type' => self::TYPE_LIST['reconnect']]);
                $this->redis->HSET(RedisKeyMap::SOCKET_USER_FD_LIST, $uid, json_encode($uidFdListArr));
            } else {
                $this->redis->HDEL(RedisKeyMap::SOCKET_USER_FD_LIST, $uid);
                $this->redis->HDEL(RedisKeyMap::SOCKET_USER_LIST, $uid);
            }
        }
    }



}
