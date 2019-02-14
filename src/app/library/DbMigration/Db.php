<?php
namespace DbMigration;

/**
* Db
*/
class Db
{
    private $_config = [
        'adapter'     => 'Mysql',
        'host'        => '127.0.0.1',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'test',
        'charset'     => 'utf8',
    ];

    public $driverName = null;

    public $pdo = null;

    public function __construct($config)
    {
        $this->_config = array_merge($this->_config, $config);

        try {
            //mysql:dbname=testdb;host=127.0.0.1

            $dsn = strtolower($this->_config['adapter']).":dbname=".$this->_config['dbname'].";".
                "host=".$this->_config['host'];
            $this->pdo = new \PDO(
                $dsn,
                $this->_config['username'],
                $this->_config['password']
            );
            $this->driverName = strtolower($this->_config['adapter']);
        } catch (\PDOException $e) {
            throw $e;
        }

        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function fetch($sql, $data = [], $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $stmt->fetch($fetchStyle);
    }

    public function fetchAll($sql, $data = [], $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $stmt->fetchAll($fetchStyle);
    }

    public function exec($sql, $data = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $stmt->rowCount();
    }

    public function getLastError()
    {
        $error['code'] = $this->pdo->errorCode();
        $error['info'] = $this->pdo->errorInfo();

        return $error;
    }

    public static function getQuoted($sql, $tablePrefix = '')
    {
        return str_replace(
            ['{{%', '{{', '}}', '[[', ']]'],
            ['`'.$tablePrefix, '`', '`', '`', '`'],
            $sql
        );
    }

    public static function addcslashes($str)
    {
        return addcslashes($str, "\000\n\r\\\032'");
    }
}
