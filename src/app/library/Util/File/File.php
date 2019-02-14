<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2019/1/8
 * Time: 11:59 AM
 */

namespace app\library\Util\File;


class File
{

    function count_line($filePath)
    {
        $fp = fopen($filePath , "rb");
        $i = 0;
        while (!feof($fp)) {
            //每次读取2M
            if ($data = fread($fp, 1024 * 1024 * 2)) {
                //计算读取到的行数
                $num = substr_count($data, "\n");
                $i += $num;
            }
        }
        fclose($fp);
        return $i;
    }
}