<?php
namespace BtcRelax\Dao;

use PDO;

final class SessionsDao extends BaseDao 
{
    public function deleteSessionById(string $sid)
    {
        $sql = 'DELETE FROM Sessions WHERE sid = :sid';
        $this->query($sql);
        $this->bind(':sid', $sid);
        return $this->execute();
    }
    
    public function insertSession(\BtcRelax\Session $session)
    {
        $sql = "INSERT INTO Sessions (sid, expires, forced_expires, ua, created, netinfo, server) VALUES ( :sid, :expires, :forced_expires, :ua, :created, :netinfo, :server)";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $session->getSessionId(), PDO::PARAM_STR);
        $statement->bindParam('expires', $session->getExpireSession(), PDO::PARAM_INT);
        $statement->bindParam('forcedExpires', $session->getForcedExpireSession(), PDO::PARAM_INT);
        $statement->bindParam('ua',$session->getUserAgent() , PDO::PARAM_STR);
        $statement->bindParam('netinfo', $session->getUserIP() , PDO::PARAM_STR);
        $statement->bindParam('server',$session->getCurrentServer(), PDO::PARAM_STR);
        $vRes = $statement->execute(); 
        return $vRes;        
    }
    
    public function selectSessionById(string $sid) {
        $sql = "SELECT sid, expires, forced_expires, ua, created, netinfo, server  FROM Sessions WHERE sid = :sid ";
        $this->query($sql);
        $this->bind(':sid', $sid);
        return $this->single();
    }
    
    public function selectSessionVars(string $sid)
    {
        $sql = "SELECT name, value, sid FROM SessionVars WHERE sid = :sid ";
        $this->query($sql);
        $this->bind(':sid', $sid );
        return $this->resultset();
    }
    
    public function insertSessionVar(string $sid, string $name, string $value)
    {
        $sql = "INSERT INTO SessionVars ( name, value, sid) VALUES ( :name, :value, :sid)";
        $this->query($sql);
        $this->bind(':sid', $sid);
        $this->bind(':name', $name);
        $this->bind(':value', $value);
        return $this->execute();        
    }
    
    public function deleteSessionVars(string $sid)
    {
        $sql = "DELETE FROM SessionVars WHERE sid = :sid";
        $this->query($sql);
        $this->bind('sid', $sid);
        return $this->execute();
    }
    
    public function updateSessionExpireTime(string $sid, string $expires)
    {
        $sql = "UPDATE Sessions SET expires = :expires WHERE sid = :sid";
        $statement = $this->getDb()->prepare($sql);
        $statement->bindParam('sid', $sid, PDO::PARAM_STR);
        $statement->bindParam('expires', $expires, PDO::PARAM_STR);
        return $statement->execute();
    }

    public function deleteExpiredSession()
    {
        $sql = "DELETE FROM Sessions WHERE forced_expires = :forced_expires";
        $this->query($sql);
        $this->bind('forced_expires', time());
        return $this->execute();
    }
    
    
 
}
