<?php
namespace BtcRelax\Model;
class Customer {

		// private members
		protected $m_CreateDate = null;
		protected $m_idCustomer = null;
		protected $m_isBaned = false;
                protected $m_ChangeDate;
                protected $m_Balance = 0;
                protected $m_Preferences;
                protected $m_totalOrdersCount = 0;
                protected $m_lostCount = 0;

		public function __construct() {
		}

		/**
		* Getters and Setters
		*/
               function getBalance() {
                   return $this->m_Balance;
               }

               function setBalance($m_Balance) {
                   $this->m_Balance = $m_Balance;
               }

               
                
               public function getLostCount() {
                   return $this->m_lostCount;
               }

               public function setLostCount($m_lostCount) {
                   $this->m_lostCount = $m_lostCount;
               }

                               
		public function getCreateDate() {
			return $this->m_CreateDate;
		}
                
                public function getCreateDateFormated() {
                    return \BtcRelax\Utils::formatDate($this->m_CreateDate);
                }

		public function setCreateDate($CreateDate) {
			$this->m_CreateDate = $CreateDate;
		}

		public function getIdCustomer() {
			return $this->m_idCustomer;
		}

		public function setIdCustomer($idCustomer) {
			$this->m_idCustomer = $idCustomer;
		}

		public function getIsBaned() {
			return $this->m_isBaned;
		}

		public function setIsBaned($isBaned) {
			$this->m_isBaned = $isBaned;
		}


		
		/**
		* Methods
		*/
               public function getChangeDate() {
                   return $this->m_ChangeDate;
               }

               public function getPreferences() {
                   return $this->m_Preferences;
               }

               public function setChangeDate($m_ChangeDate) {
                   $this->m_ChangeDate = $m_ChangeDate;
                   return $this;
               }

               public function setPreferences($m_Preferences) {
                   $this->m_Preferences = $m_Preferences;
                   return $this;
               }

                public function getTotalOrdersCount() {
                    return $this->m_totalOrdersCount;
                }
    
                public function setTotalOrdersCount($pTotalOrdersCount) {
                    $this->m_totalOrdersCount = $pTotalOrdersCount;
                }
                                         
		public function __toString() {
                    $result = $this->getArray();    
                    return \BtcRelax\Utils::toJson($result); 
		}
                
                public function getArray() {
                        $result = ["Id" => $this->m_idCustomer ];
                        $result += ["Registered" => $this->getCreateDateFormated() ];
                        $result += ["isBaned" => $this->m_isBaned === false? "false" : "true" ];
                        $result += ["OrderCount" => $this->m_totalOrdersCount ];
                        $result += ["LostCount" => $this->m_lostCount ];
                        return $result;
                }

}

?>
