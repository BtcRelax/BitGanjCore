<?php
namespace BtcRelax\Dao;

final class IdentifierSearchCriteria {

    /**
     * @var array
     */
    private $vIdIdentity;
    private $vIdentTypeCode;
    private $vIdentityKey;
    private $vIdCustomer = null;
        
    public function __construct(array $params = null) {
        if ($params !== null) 
            {
                $this->parseParams($params);
            }
    }

    public function getIdIdentity() {
        return $this->vIdIdentity;
    }

    public function getIdentTypeCode() {
        return $this->vIdentTypeCode;
    }

    public function getIdentityKey() {
        return $this->vIdentityKey;
    }

    public function getIdCustomer() {
        return $this->vIdCustomer;
    }

    public function setIdIdentity($vIdIdentity) {
        $this->vIdIdentity = $vIdIdentity;
    }

    public function setIdentTypeCode($vIdentTypeCode) {
        $this->vIdentTypeCode = $vIdentTypeCode;
    }

    public function setIdentityKey($vIdentityKey) {
        $this->vIdentityKey = $vIdentityKey;
    }

    public function setIdCustomer($vIdCustomer) {
        $this->vIdCustomer = $vIdCustomer;
    }

    
    private function parseParams(array $params)
    {
        foreach ($params as $key => $value) {
                if ($key == 'IdentTypeCode' ) { $this->setIdentTypeCode($value); }
                if ($key == 'IdentityKey' ) { $this->setIdentityKey($value); }
                if ($key == 'IdCustomer') { $this->setIdCustomer($value); }           
                if ($key == 'IdIdentity') { $this->setIdIdentity($value); }               
                }
            return $this;
                
        }
 }

