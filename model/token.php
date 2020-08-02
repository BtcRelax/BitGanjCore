<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BtcRelax\Model;

/**
 * Description of token
 *
 * @author Chronos
 */
class token {
    
    private $tokenId;
    private $idCustomer;
    private $RightCode;
    private $TokenKey;
    
    public function __construct() {
        
    }
    
    public function setTokenId($tokenId) {
        $this->tokenId = $tokenId;
        return $this;
    }

    public function setIdCustomer($idCustomer) {
        $this->idCustomer = $idCustomer;
        return $this;
    }

    public function setRightCode($RightCode) {
        $this->RightCode = $RightCode;
        return $this;
    }

    public function setTokenKey($TokenKey) {
        $this->TokenKey = $TokenKey;
        return $this;
    }

        
    public function getTokenId() {
        return $this->tokenId;
    }

    public function getIdCustomer() {
        return $this->idCustomer;
    }

    public function getRightCode() {
        return $this->RightCode;
    }

    public function getTokenKey() {
        return $this->TokenKey;
    }

    
    //put your code here
}
