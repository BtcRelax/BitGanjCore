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

    public function __construct($db = null, $autocommit = true)
    {
        $this->autocommit = $autocommit;
        if ($db !== null) {
            $this->db = $db;
        } else {
            try {
                $this->db = new \PDO(\sprintf("mysql:dbname=%s;host=%s", DB_NAME, DB_HOST), DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;"));
                $this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT, $this->getAutocommit() === true ? 1 : 0);
            } catch (\PDOException $ex) {
                \BtcRelax\Log::general(new \Exception('DB connection error: ' . $ex->getMessage()), \BtcRelax\Log::FATAL);
            }
        }
    }

    protected function getAutocommit()
    {
        return $this->autocommit;
    }

    public function getDb()
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
            \BtcRelax\Log::general(\sprintf("Error when execute sql:%s", $this->pdo_debugStrParams($statement)), \BtcRelax\Log::ERROR);
            self::throwDbError($this->getDb()->errorInfo());
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
        $statement = $this->getDb()->query($sql, PDO::FETCH_ASSOC);
        if ($statement === false) {
            self::throwDbError($this->getDb()->errorInfo());
        }
        return $statement;
    }

    public function get_numeric($val)
    {
        if (is_numeric($val)) {
            return (int) $val + 0;
        }
        return 0;
    }

    protected static function throwDbError(array $errorInfo)
    {
        $error_message = 'DB error [' . $errorInfo[0] . ', ' . $errorInfo[1] . ']: ' . $errorInfo[2];
        throw new Exception($error_message);
    }

    protected static function formatDateTime(\DateTime $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function formatBoolean($bool)
    {
        return $bool ? 1 : 0;
    }
}
