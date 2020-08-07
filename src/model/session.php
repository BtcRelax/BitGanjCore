<?php
namespace BtcRelax\Model;

class Session
{
    
    private $sid;
    private $expires;
    private $forced_expires;
    private $ua; 
    private $created; 
    private $netinfo; 
    private $server;

    public function __construct()
    {
    }


    
    public function __toString()
    {
        $result = $this->getArray();
        return \BtcRelax\Utils::toJson($result);
    }

}
