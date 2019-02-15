<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 18-07-06
 * Time: 09:51
 */

namespace Util\Tools;


use Util\Auth\JwtAuth;

class Http
{

    /**
     * 发送GET请求
     * @param $url
     * @param bool $format
     * @return mixed
     * @throws \Exception
     */
    public static function sendGet($url, $format = false)
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $response = curl_exec($ch);//接收返回信息
        if (curl_errno($ch) == 28) {//出错则显示错误信息
            throw new \Exception('超时请求url:' . $url);
        }
        curl_close($ch); //关闭curl链接
        if ($format) {
            $response = json_decode($response, true);
        }

        return $response;
    }


    /**
     * 构建参数
     * @param $url
     * @param $data
     * @return string
     */
    public static function buildUrl($url, $data)
    {
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        return $url;
    }

    /**
     * 发送POST请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return mixed
     * @throws \Exception
     */
    public static function sendPost($url, $post_data)
    {
        $postData = http_build_query($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);//接收返回信息
        if (curl_errno($ch) == 28) {//出错则显示错误信息
            throw new \Exception('超时请求url:' . $url);
        }
        curl_close($ch); //关闭curl链接
        return $response;
    }

    /**
     * 发送POST请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return mixed
     * @throws \Exception
     */
    public static function sendPostJson($url, $post_data)
    {
        $postData = json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            )
        );
        $response = curl_exec($ch);//接收返回信息
        if (curl_errno($ch) == 28) {//出错则显示错误信息
            throw new \Exception('超时请求url:' . $url);
        }
        curl_close($ch); //关闭curl链接
        return $response;
    }


    /**
     * 获取header头token
     * @return int
     */
    public static function getAuthorizationUid()
    {
        if (php_sapi_name() == 'cli') {
            return 'cli';
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-',
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        if (empty($headers)) {
            throw new \Error('无法获取到UID');
        }

        if (!isset($headers['Authorization']) && strpos($headers['Host'], 'crmapi-allot') !== false) {
            return 'web';
        }

        if (!isset($headers['Authorization'])) {
            return 'test';
        }


        $uid = 0;
        $token = $headers['Authorization'];
        $res = JwtAuth::decodeToken($token);
        if (isset($res->uid) && !empty($res->uid)) {
            $uid = $res->uid;
        }

        if (empty($uid)) {
            throw new \Error('UID无参数');
        }

        return $uid;
    }


}