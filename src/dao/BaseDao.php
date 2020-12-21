<?php

namespace BtcRelax\Dao;

use \PDO;

class BaseDao 
{
    protected ?\PDO $db = null; 
    private $error;
    private $stmt;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public static function prepareConnection(string $db_host, string $db_name, string $db_user, string $db_pass):PDO
    {
        $options = array(
				PDO::ATTR_PERSISTENT    => true,
				PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION,
                                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET time_zone='+03:00';"
                        );
        return new \PDO(\sprintf("mysql:dbname=%s;host=%s", $db_name, $db_host) , $db_user, $db_pass, $options);        
    }


    public function setAutocommit(bool $isAutocommit)
    {
        $this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT, \BtcRelax\Utils::formatBoolean($isAutocommit) );
    }

    public function getAutocommit():bool
    {
        return $this->db->getAttribute(\PDO::ATTR_AUTOCOMMIT);
    }

    public function getDb():PDO
    {
        return $this->db;
    }

    public function addToFilter($filter, $newWhere):string
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

    public function query($query)
    {
        $this->stmt = $this->db->prepare($query);
        //$statement = $this->db->query($sql, PDO::FETCH_ASSOC);
        //if ($statement === false) {
        //    \BtcRelax\Logger::general(\sprintf("Error message %s  when query sql:%s ",$this->db->errorInfo(), $sql), \BtcRelax\Logger::ERROR);
        //}
        //return $statement;
    }

    /**
    * Return MySQL server time
    */
    
    public function now()
    {
        $result = $this->query("SELECT now() as 'DBTime'")->fetch();
        return $result;
    }
    
    
    public function get_numeric($val):int
    {
        if (is_numeric($val)) {
            return (int) $val + 0;
        }
        return 0;
    }
    
    public function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
		break;
            default:
                $type = PDO::PARAM_STR;
            }
	}
	$this->stmt->bindValue($param, $value, $type);  
    }

    public function execute(){
	return $this->stmt->execute();
    }    
		
    public function resultset():array {
        $this->execute();
	return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
		
    public function single(){
        $this->execute();
	return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
		
    public function rowCount(){
        return $this->stmt->rowCount();
    }
		
    public function lastInsertId(){
        return $this->db->lastInsertId();
    }
    
    public function beginTransaction(){
        return $this->db->beginTransaction();
    }
		
    public function endTransaction(){
        return $this->db->commit();
    }
		
    public function cancelTransaction(){
        return $this->db->rollBack();
    }
		
    public function debugDumpParams(){
        return $this->stmt->debugDumpParams();
    }
		
    public function close(){
        $this->db = null;
    }
}
