<?php
namespace BtcRelax\Dao;

use PDO;

final class SessionsDao extends BaseDao 
{
    public function deleteSessionById(string $sid)
    {
        $sql = 'DELETE FROM Sessions WHERE sid = :sid';
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        return $statement->execute();
    }
    
    public function insertSession(\BtcRelax\Session $session)
    {
        $sql = "INSERT INTO Sessions (sid, expires, forced_expires, ua, created, netinfo, server) VALUES ( :sid, :expires, :forced_expires, :ua, :created, :netinfo, :server)";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        $statement->bindParam('expires', $expireTime, PDO::PARAM_INT);
        $statement->bindParam('forcedExpires', $this->forcedExpire, PDO::PARAM_INT);
        $statement->bindParam('ua',$vUA , PDO::PARAM_STR);
        $statement->bindParam('netinfo', \BtcRelax\Utils::getIpAddress() , PDO::PARAM_STR);
        $statement->bindParam('server',$vServer, PDO::PARAM_STR);
        $vRes = $statement->execute(); 
        return $vRes;        
    }
    
    public function selectSessionById(string $sid) {
        $sql = "SELECT sid, expires, forced_expires, ua, created, netinfo, server  FROM Sessions WHERE sid = :sid ";
        $statement = $this->getDb()->prepare($sql);
        
    }
    
    public function selectSessionVars(string $sid)
    {
        $sql = "SELECT name, value, sid FROM SessionVars WHERE sid = :sid ";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        
    }
    
    public function insertSessionVar(string $sid, string $name, string $value)
    {
        $sql = "INSERT INTO SessionVars ( name, value, sid) VALUES ( :name, :value, :sid)";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        $statement->bindParam('name', $name, PDO::PARAM_STR);
        $statement->bindParam('value', $value, PDO::PARAM_STR);
        return $statement->execute();        
    }
    
    public function deleteSessionVar(string $sid, string $name)
    {
        $sql = "DELETE FROM WHERE sid = :sid AND name = :name";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        $statement->bindParam('name', $name, PDO::PARAM_STR);
        return $statement->execute();
    }
    
    public function updateSessionExpireTime(string $sid, string $expires)
    {
        $sql = "UPDATE Sessions SET expires = :expires WHERE sid = :sid";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        $statement->bindParam('expires', $expires, PDO::PARAM_STR);
        return $statement->execute();
    }

 
}
