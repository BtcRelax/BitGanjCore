<?php
  namespace BtcRelax\Mapping;

    final class CustomerPropertyMapper {

	private function __construct() {
	}


	public static function map(\BtcRelax\Model\CustomerProperty $custProperty, array $properties) {
               
		if (array_key_exists('idCustomer', $properties)) {
			$custProperty->setIdCustomer($properties['idCustomer']);
		}
                
                if (array_key_exists('PropertyTypeCode', $properties)) {
			$custProperty->setPropertyTypeCode($properties['PropertyTypeCode']);
		}
                
                if (array_key_exists('PropertyTypeTitle', $properties)) {
			$custProperty->setPropertyTypeTitle($properties['PropertyTypeTitle']);
		}
                
                if (array_key_exists('PropertyValue', $properties)) {
			$custProperty->setPropertyValue($properties['PropertyValue']);
		}
	}
}
