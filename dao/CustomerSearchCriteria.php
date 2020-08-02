<?php namespace BtcRelax\Dao;

 final class CustomerSearchCriteria {
	private $isRights = [];
        private $isProperties = [];
        private $isBanned = false;
        private $customerId;
        
         
        public function __construct(array $params = null) {
            if ($params !== null) 
            {
                $this->parseParams($params);
            }
        }
        
        public function getCustomerId() {
            return $this->customerId;
        }
        public function setCustomerId($customerId) {
            $this->customerId = $customerId;
            return $this;
        }
        
        public function getIsRights() {
            return $this->isRights;
        }

        public function getIsProperties() {
            return $this->isProperties;
        }

        public function getIsBanned() {
            return $this->isBanned;
        }

        public function setIsRights($isRights) {
            $this->isRights = $isRights;
            return $this;
        }

        public function setIsProperties($isProperties) {
            $this->isProperties = $isProperties;
            return $this;
        }

        public function setIsBanned($isBanned) {
            $this->isBanned = $isBanned;
            return $this;
        }

    public function parseParams($params) {
        foreach ($params as $key => $value) {
                if ($key == 'id' ) { $this->setCustomerId($value); }
                if ($key == 'isBanned' ) { $this->setIsBanned($value); }               
                }
        return $this;
    }

}
