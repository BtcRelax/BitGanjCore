<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BtcRelax;

class Request {
    private $id;
    private $header;
    private $body;

    function __construct() {
        $this->id = \BtcRelax\Utils::generateNonce();
        $this->header = \BtcRelax\Utils::getHeaders();
        $this->body = \BtcRelax\Utils::getRequestParams();
    }
    
    public function isCanAcceptHtml() {
        if (\array_key_exists("Accept", $this->header)) {
            $vAS = $this->header["Accept"];
            return \strpos($vAS, "text/html") !== false ? true: false ;
        } else {
            return false;
        }
    }
    
    
    public function getHeaderByKey($keyName) {
        $result = false;
        if (array_key_exists($keyName, $this->header)) {
            $result = $this->header[$keyName];
        }
        return $result;
    }

    public function getParamByKey($keyName) {
        $result = false;
        if (array_key_exists($keyName, $this->body)) {
            $result = $this->body[$keyName];
        }
        return $result;
    }
    
    private function isApiCall() {
        return \array_key_exists("api",$this->body) || \array_key_exists("api", $this->header);
    }

    public function getApiClassName():string {
        if ($this->isApiCall()) {
            return \sprintf("\BtcRelax\api\%s", $this->getBody()["api"] );
        }
    }
    
    public function getId() {
        return $this->id;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getBody() {
        return $this->body;
    }

}
