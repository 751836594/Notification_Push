<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/12/3
 * Time: 2:59 PM
 */

namespace Util\Tools;



class Validation
{

    public static function isString($string,$len = 20)
    {
        if (is_string($string) && mb_strlen($string) <= $len) {
            return true;
        }
        return false;
    }


    public static function verifyLen($string,$len = 20)
    {
        if (mb_strlen($string) <= $len) {
            return true;
        }
        return false;
    }

    public static function isInt($id)
    {
        if (is_numeric($id)) {
            return true;
        }
        return false;
    }

    public static function isFloat($id)
    {
        if (is_numeric($id)) {
            return true;
        }
        return false;
    }


    public static function isMobile($mobile)
    {

        if (empty($mobile)) {
            return false;
        }

        $exp = "/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|16[0-9]{9}$|19[0-9]{9}$|14[57]{1}[0-9]{8}$|^17[0-9]{9}$/";
        if (preg_match($exp, $mobile)) {
            return true;
        }

        return false;
    }


}