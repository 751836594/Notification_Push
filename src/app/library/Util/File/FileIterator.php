<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2019/1/7
 * Time: 10:52 AM
 */

namespace Util\File;


use Exception;
use Iterator;

/**
 * Class FileIterator
 * @package Util\File
 */
class FileIterator implements Iterator
{


    private $fp;

    private $lineNumber;

    private $lineContent;

    /**
     * FileIterator constructor.
     * @param $file
     * @throws Exception
     */
    public function __construct($file)
    {
        $fp = fopen($file, "r");
        if (!$fp) {
            throw new Exception("「{$file}」不能打开");
        }
        $this->fp = $fp;
    }

    /**
     * @return mixed|string
     */
    public function current()
    {
        $this->lineContent = fgets($this->fp);
        return rtrim($this->lineContent, "\n");
    }

    /**
     *
     */
    public function next()
    {
        $this->lineNumber++;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->lineNumber;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return feof($this->fp) ? false : true;
    }

    /**
     *
     */
    public function rewind()
    {
        $this->lineNumber = 1;
    }

}