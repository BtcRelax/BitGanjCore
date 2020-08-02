<?php
namespace BtcRelax\Model;
class CustomerProperty {

		// private members
		protected $m_idCustomer = null;
                protected $m_PropertyValue = null;
                protected $m_PropertyTypeCode = null;
                protected $m_PropertyTypeTitle = null;

		public function __construct() {
		}

		public static function WithParams($idCustomer, $PropertyTypeCode, $PropertyValue) {
			$instance = new self();
                        $instance->m_idCustomer = $idCustomer;
                        $instance->m_PropertyTypeCode = $PropertyTypeCode;
                        $instance->m_PropertyValue = $PropertyValue;
			return $instance;
		}
                 
		public function __toString() {
			return \sprintf("Customer Id:%s, PropertyCode:%s", $this->m_idCustomer,$this->m_PropertyTypeCode); 
		}
                
                public function getIdCustomer() {
                    return $this->m_idCustomer;
                }

                public function setIdCustomer($m_idCustomer) {
                    $this->m_idCustomer = $m_idCustomer;
                }

                public function getPropertyValue() {
                    return $this->m_PropertyValue;
                }

                public function getPropertyTypeCode() {
                    return $this->m_PropertyTypeCode;
                }

                public function getPropertyTypeTitle() {
                    return $this->m_PropertyTypeTitle;
                }

                public function setPropertyValue($m_PropertyValue) {
                    $this->m_PropertyValue = $m_PropertyValue;
                }

                public function setPropertyTypeCode($m_PropertyTypeCode) {
                    $this->m_PropertyTypeCode = $m_PropertyTypeCode;
                }

                public function setPropertyTypeTitle($m_PropertyTypeTitle) {
                    $this->m_PropertyTypeTitle = $m_PropertyTypeTitle;
                }

}


