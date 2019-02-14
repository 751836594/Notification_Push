<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2018/11/30
 * Time: 1:57 PM
 */

namespace Util\Tools;

class ArrayHelper
{
    /**
     * 合并数组
     * @return mixed
     */
    public static function arrayMerge()
    {

        $arrays = func_get_args();

        if (count($arrays) == 1) {
            return $arrays[0];
        }


        foreach ($arrays as $key => $array) {
            if (!is_array($array)) {
                unset($arrays[$key]);
            }
        }

        $final = array_shift($arrays);


        foreach ($arrays as $b) {
            foreach ($final as $key => $value) {
                if (!isset($b[$key])) {

                    $final[$key] = $value;
                } else {
                    if (!is_array($value) && !empty($b[$key])) {
                        $final[$key] = $b[$key];
                    } else {
                        if (is_array($value) && is_array($b[$key])) {
                            $final[$key] = self::arrayMerge($value, $b[$key]);
                        } else {
                            $final[$key] = $b[$key];
                        }
                    }

                }

            }

            foreach ($b as $key => $value) {
                if (!isset($final[$key])) {
                    $final[$key] = $value;
                }
            }

        }

        return $final;

    }
}