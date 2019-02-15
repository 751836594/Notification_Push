<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/11/30
 * Time: 2:30 PM
 */

namespace Util\Tools;


class RedisKeyMap
{
    const SOCKET_CONNECT_LIST = 'socket_connect_list'; //存储的链接fd列表
    const SOCKET_USER_LIST = 'socket_user_list';//存储的客户链接列表
    const SOCKET_USER_FD_LIST = 'socket_user_fd_list';//存储的客户链接列表
}