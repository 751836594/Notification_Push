<?php
/**
 * 获取ip地址
 * User: steven
 * Date: 18-07-06
 * Time: 09:51
 *
 * 参考: https://raw.githubusercontent.com/zendframework/zf2/master/library/Zend/Http/PhpEnvironment/RemoteAddress.php
 *
 */

namespace Util\Tools;


class Ip
{


    /**
     * 获取ip
     * @return string
     */
    public static function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED'];
                } else {
                    if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
                    } else {
                        if (isset($_SERVER['HTTP_FORWARDED'])) {
                            $ip = $_SERVER['HTTP_FORWARDED'];
                        } else {
                            if (isset($_SERVER['REMOTE_ADDR'])) {
                                $ip = $_SERVER['REMOTE_ADDR'];
                            } else {
                                $ip = 'UNKNOWN';
                            }
                        }
                    }
                }
            }
        }
        return $ip;
    }

    /**
     * 备份
     * @return string
     */
    public static function getIp()
    {
        static $ipObj = false;
        if (!$ipObj) {
            $ipObj = new Ip();
            //设置反向代理的ip
            $ipObj->setUseProxy(true);
            $ipObj->setTrustedProxies(array());
        }
        $ip = $ipObj->getIpAddress();

        return $ip;
    }

    /**
     * Whether to use proxy addresses or not.
     *
     * As default this setting is disabled - IP address is mostly needed to increase
     * security. HTTP_* are not reliable since can easily be spoofed. It can be enabled
     * just for more flexibility, but if user uses proxy to connect to trusted services
     * it's his/her own risk, only reliable field for IP address is $_SERVER['REMOTE_ADDR'].
     *
     * @var bool
     */
    protected $useProxy = false;

    /**
     * List of trusted proxy IP addresses
     *
     * @var array
     */
    protected $trustedProxies = array();

    /**
     * HTTP header to introspect for proxies
     *
     * @var string
     */
    protected $proxyHeader = 'HTTP_X_FORWARDED_FOR';


    /**
     * Changes proxy handling setting.
     *
     * This must be static method, since validators are recovered automatically
     * at session read, so this is the only way to switch setting.
     *
     * @param  bool $useProxy Whether to check also proxied IP addresses.
     * @return RemoteAddress
     */
    public function setUseProxy($useProxy = true)
    {
        $this->useProxy = $useProxy;
        return $this;
    }

    /**
     * Checks proxy handling setting.
     *
     * @return bool Current setting value.
     */
    public function getUseProxy()
    {
        return $this->useProxy;
    }

    /**
     * Set list of trusted proxy addresses
     *
     * @param  array $trustedProxies
     * @return RemoteAddress
     */
    public function setTrustedProxies(array $trustedProxies)
    {
        $this->trustedProxies = $trustedProxies;
        return $this;
    }

    /**
     * Set the header to introspect for proxy IPs
     *
     * @param  string $header
     * @return RemoteAddress
     */
    public function setProxyHeader($header = 'X-Forwarded-For')
    {
        $this->proxyHeader = $this->normalizeProxyHeader($header);
        return $this;
    }

    /**
     * Returns client IP address.
     *
     * @return string IP address.
     */
    public function getIpAddress()
    {
        $ip = $this->getIpAddressFromProxy();
        if ($ip) {
            return $ip;
        }

        // direct IP address
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    /**
     * Attempt to get the IP address for a proxied client
     *
     * @see http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.2
     * @return false|string
     */
    protected function getIpAddressFromProxy()
    {
        if (!$this->useProxy) {
            return false;
        }
        $header = $this->proxyHeader;
        if (!isset($_SERVER[$header]) || empty($_SERVER[$header])) {
            return false;
        }
        // Extract IPs
        $ips = explode(',', $_SERVER[$header]);
        return array_pop($ips);
    }

    /**
     * Normalize a header string
     *
     * Normalizes a header string to a format that is compatible with
     * $_SERVER
     *
     * @param  string $header
     * @return string
     */
    protected function normalizeProxyHeader($header)
    {
        $header = strtoupper($header);
        $header = str_replace('-', '_', $header);
        if (0 !== strpos($header, 'HTTP_')) {
            $header = 'HTTP_' . $header;
        }
        return $header;
    }

    /**
     * 获取IP的省市
     * @param $ip
     * @return string
     * @throws \Exception
     */
    public static function getCity($ip)
    {
        $ipContent = Http::sendGet("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip={$ip}");
        $jsonData = explode("=", $ipContent);
        if (isset($jsonData[1])) {
            $jsonAddress = substr($jsonData[1], 0, -1);
            $data = json_decode($jsonAddress);
            return isset($data->city) ? $data->city : '';
        } else {
            return '';
        }
    }


    /**
     * 获取IP的区
     * @param $ip
     * @return string
     * @throws \Exception
     */
    public static function getDistrict($ip)
    {
        $ipContent = Http::sendGet("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip={$ip}");
        $jsonData = explode("=", $ipContent);
        $jsonAddress = substr($jsonData[1], 0, -1);
        $data = json_decode($jsonAddress);
        return isset($data->district) ? $data->district : '';
    }

    /**
     * 获取IP地址信息
     * @param $ip
     * @return array|string
     * @throws \Exception
     */
    public static function getAddress($ip)
    {
        $ipContent = Http::sendGet("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip={$ip}");
        $jsonData = explode("=", $ipContent);
        $jsonAddress = substr($jsonData[1], 0, -1);
        $data = array(json_decode($jsonAddress));
        return isset($data) ? $data : '';
    }
}