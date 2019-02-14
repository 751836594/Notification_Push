<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/7/13
 * Time: 下午2:45
 */

namespace Util\Auth;


use Firebase\JWT\JWT;
use Util\Common\Config;

class JwtAuth
{
    /**
     * 验证token
     * @param $token
     * @return array
     */
    public static function verifyToken($token)
    {
        $res = self::decodeToken($token);
        if (isset($res->uid) && !empty($res->uid)) {
            return ['status' => true, 'uid' => $res->uid,'department_id' => $res->department_id];
        }

        $errMsg = Config::getErrorMsg(21316);
        return ['status' => false, 'err_msg' => $errMsg];
    }

    /**
     * 生成AuthCode
     * @param $data
     * @return string
     */
    public static function generateToken($data)
    {
        $privateKey = Config::get('privateKey');
        $token = JWT::encode($data, $privateKey, 'RS256');
        return $token;
    }


    /**
     * 解析token
     * @param $token
     * @return object
     */
    public static function decodeToken($token)
    {
        $publicKey = Config::get('publicKey');
        $decoded = JWT::decode($token, $publicKey, array('RS256'));
        return $decoded;
    }

}