<?php

namespace BtcRelax\Dao;

use \PDO;
use \Exception;

class BaseDao
{
    protected $db = null;
    protected $autocommit = true;

    public function __destruct()
    {
        $this->db = null;
    }

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public static function prepareConnection(string $db_host, string $db_name, string $db_user, string $db_pass):PDO
    {
        return new \PDO("mysql:dbname=" . $db_name  . ";host=" . $db_host , $db_user, $db_pass, 
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET time_zone='+03:00';"));        
    }


    public function setAutocommit(bool $isAutocommit)
    {
        $this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT, \BtcRelax\Utils::formatBoolean($isAutocommit) );
    }

    public function getAutocommit()
    {
        return $this->db->getAttribute(\PDO::ATTR_AUTOCOMMIT);
    }


    public function getDb():PDO
    {
        return $this->db;
    }

    public function addToFilter($filter, $newWhere)
    {
        if (empty($filter)) {
            return \sprintf('WHERE %s', $newWhere);
        } else {
            return \sprintf('%s AND %s', $filter, $newWhere);
        }
    }

    public function executeStatement(\PDOStatement $statement, array $params)
    {
        if ($statement->execute($params) === false) {
            \BtcRelax\Logger::general(\sprintf("Error message %s  when execute sql:%s ",$this->db->errorInfo(), $this->pdo_debugStrParams($statement)), \BtcRelax\Logger::ERROR);
        }
    }

    public function pdo_debugStrParams($stmt)
    {
        ob_start();
        $stmt->debugDumpParams();
        $r = ob_get_contents();
        ob_end_clean();
        return $r;
    }

    public function query($sql)
    {
        $statement = $this->db->query($sql, PDO::FETCH_ASSOC);
        if ($statement === false) {
            \BtcRelax\Logger::general(\sprintf("Error message %s  when query sql:%s ",$this->db->errorInfo(), $sql), \BtcRelax\Logger::ERROR);
        }
        return $statement;
    }

    public function get_numeric($val):int
    {
        if (is_numeric($val)) {
            return (int) $val + 0;
        }
        return 0;
    }
}
