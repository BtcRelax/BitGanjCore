<?php
  namespace BtcRelax\Mapping;
  
  use \DateTime;

final class CustomerMapper {

	private function __construct() {
	}

	public static function map(\BtcRelax\Model\Customer $cust, array $properties) {
            if (array_key_exists('CreateDate', $properties)) {
			$createdOn = self::createDateTime($properties['CreateDate']);
			if ($createdOn)
			{ $cust->setCreateDate($createdOn); }}               
            if (array_key_exists('ChangeDate', $properties)) {
			$changedOn = self::createDateTime($properties['ChangeDate']);
			if ($changedOn)
			{
				$cust->setChangeDate($changedOn);  
			}}                
            if (array_key_exists('idCustomer', $properties)) {
			$cust->setIdCustomer($properties['idCustomer']);
		}                
            if (array_key_exists('Preferences', $properties)) {
			$cust->setPreferences($properties['Preferences']);
		}
            if (array_key_exists('isBaned', $properties)) {  
			$cust->setIsBaned(boolval($properties['isBaned']));
		}
            if (array_key_exists('TotalOrdersCount', $properties)) {  
			$cust->setTotalOrdersCount(intval($properties['TotalOrdersCount']));
		}
            if (array_key_exists('LostsCount', $properties)) {  
			$cust->setLostCount(intval($properties['LostsCount']));
		}
        }

	private static function createDateTime($input) {
		return DateTime::createFromFormat('Y-n-j H:i:s', $input);
	}

}

  
  
?>
